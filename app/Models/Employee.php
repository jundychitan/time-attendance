<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory;

    /**
     * Hour boundary to distinguish night shift from day shift.
     * Checkins after this hour may be overnight shift starts.
     * Checkins before this hour on the next day may be overnight time-outs.
     */
    public const OVERNIGHT_CUTOFF_HOUR = 12;

    /**
     * Maximum hours for a valid shift. If the gap between first and last
     * checkin is >= this value, the last checkin is not treated as a time-out.
     */
    public const MAX_SHIFT_HOURS = 16;

    protected $fillable = [
        'id_number',
        'first_name',
        'last_name',
        'department',
        'position',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Checkin, $this>
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Compute attendance for a single date, including overnight shift pairing.
     *
     * @return array{date: string, time_in: ?string, time_out: ?string, time_out_next_day: bool, total_hours: ?float}
     */
    public function attendanceForDate(Carbon|string $date): array
    {
        $date = Carbon::parse($date);

        $checkins = $this->checkins()
            ->whereDate('captured_at', $date)
            ->orderBy('captured_at')
            ->get();

        $overnightCheckout = null;

        // If the last (or only) checkin is after the cutoff, look for a next-day checkout
        $lastCheckin = $checkins->last();
        if ($lastCheckin && $lastCheckin->captured_at->hour >= self::OVERNIGHT_CUTOFF_HOUR) {
            $needsOvernightPairing = $checkins->count() === 1
                || ($checkins->count() > 1 && $this->shouldApplySixteenHourRule($checkins->first(), $lastCheckin));

            if ($needsOvernightPairing) {
                $overnightCheckout = $this->findOvernightCheckout($date, $lastCheckin);
            }
        }

        return self::computeAttendance($date, $checkins, $overnightCheckout);
    }

    /**
     * Compute attendance for a date range, including overnight shift pairing.
     *
     * @return array<int, array{date: string, time_in: ?string, time_out: ?string, time_out_next_day: bool, total_hours: ?float}>
     */
    public function attendanceForRange(Carbon|string $start, Carbon|string $end): array
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();
        $nextDay = $end->copy()->addDay()->endOfDay();

        // Fetch one extra day for potential overnight time-outs
        $allCheckins = $this->checkins()
            ->whereBetween('captured_at', [$start, $nextDay])
            ->orderBy('captured_at')
            ->get()
            ->groupBy(fn (Checkin $checkin) => $checkin->captured_at->toDateString());

        $attendance = [];
        $consumedDates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateKey = $current->toDateString();
            $dayCheckins = $allCheckins->get($dateKey, collect());

            // Remove checkins that were consumed as overnight time-outs
            if (isset($consumedDates[$dateKey])) {
                $consumedId = $consumedDates[$dateKey];
                $dayCheckins = $dayCheckins->reject(fn ($c) => $c->id === $consumedId);
            }

            $overnightCheckout = null;
            $lastCheckin = $dayCheckins->last();

            if ($lastCheckin && $lastCheckin->captured_at->hour >= self::OVERNIGHT_CUTOFF_HOUR) {
                $needsOvernightPairing = $dayCheckins->count() === 1
                    || ($dayCheckins->count() > 1 && $this->shouldApplySixteenHourRule($dayCheckins->first(), $lastCheckin));

                if ($needsOvernightPairing) {
                    $nextDateKey = $current->copy()->addDay()->toDateString();
                    $nextDayCheckins = $allCheckins->get($nextDateKey, collect());

                    // Find first checkin next day before cutoff
                    $candidate = $nextDayCheckins->first(function (Checkin $c) {
                        return $c->captured_at->hour < self::OVERNIGHT_CUTOFF_HOUR;
                    });

                    if ($candidate) {
                        $gap = $lastCheckin->captured_at->floatDiffInHours($candidate->captured_at);
                        if ($gap < self::MAX_SHIFT_HOURS) {
                            $overnightCheckout = $candidate;
                            $consumedDates[$nextDateKey] = $candidate->id;
                        }
                    }
                }
            }

            $attendance[] = self::computeAttendance($current, $dayCheckins, $overnightCheckout);
            $current->addDay();
        }

        return $attendance;
    }

    /**
     * Find an overnight checkout on the next day for a given shift start.
     */
    private function findOvernightCheckout(Carbon $date, Checkin $shiftStart): ?Checkin
    {
        $nextDay = $date->copy()->addDay();

        $candidate = $this->checkins()
            ->whereDate('captured_at', $nextDay)
            ->where('captured_at', '<', $nextDay->copy()->setHour(self::OVERNIGHT_CUTOFF_HOUR))
            ->orderBy('captured_at')
            ->first();

        if (! $candidate) {
            return null;
        }

        $gap = $shiftStart->captured_at->floatDiffInHours($candidate->captured_at);

        return $gap < self::MAX_SHIFT_HOURS ? $candidate : null;
    }

    /**
     * Check if the 16-hour rule should trigger (gap too large for a valid shift).
     */
    private function shouldApplySixteenHourRule(Checkin $first, Checkin $last): bool
    {
        return $first->captured_at->floatDiffInHours($last->captured_at) >= self::MAX_SHIFT_HOURS;
    }

    /**
     * Compute attendance record from a collection of checkins for a single day.
     *
     * @return array{date: string, time_in: ?string, time_out: ?string, time_out_next_day: bool, total_hours: ?float}
     */
    public static function computeAttendance(Carbon $date, \Illuminate\Support\Collection $checkins, ?Checkin $overnightCheckout = null): array
    {
        $first = $checkins->first();
        $last = $checkins->count() > 1 ? $checkins->last() : null;
        $timeOutNextDay = false;

        // Apply 16-hour rule: if gap is too large, discard last as time-out
        if ($first && $last) {
            $gap = $first->captured_at->floatDiffInHours($last->captured_at);
            if ($gap >= self::MAX_SHIFT_HOURS) {
                $last = null;
            }
        }

        // Use overnight checkout if no valid same-day time-out
        if ($first && ! $last && $overnightCheckout) {
            $last = $overnightCheckout;
            $timeOutNextDay = true;
        }

        // Fall back to manual_time_out if still no time-out
        $totalHours = null;
        $timeOut = $last?->captured_at;

        if ($first && ! $timeOut && $first->manual_time_out) {
            $timeOut = $first->manual_time_out;
        }

        if ($first && $timeOut) {
            $totalHours = round($first->captured_at->floatDiffInHours($timeOut), 2);
        }

        return [
            'date' => $date->toDateString(),
            'time_in' => $first?->captured_at->toTimeString(),
            'time_out' => $timeOut?->toTimeString(),
            'time_out_next_day' => $timeOutNextDay,
            'total_hours' => $totalHours,
            'checkin_id' => $first?->id,
            'manual_time_out' => $first?->manual_time_out?->toTimeString(),
        ];
    }

    /**
     * Scope to only active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search by name or ID number.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('id_number', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%");
        });
    }
}
