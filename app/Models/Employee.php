<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory;

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
     * Compute attendance for a single date.
     *
     * @return array{date: string, time_in: ?string, time_out: ?string, total_hours: ?float}
     */
    public function attendanceForDate(Carbon|string $date): array
    {
        $date = Carbon::parse($date);

        $checkins = $this->checkins()
            ->whereDate('captured_at', $date)
            ->orderBy('captured_at')
            ->get();

        return self::computeAttendance($date, $checkins);
    }

    /**
     * Compute attendance for a date range.
     *
     * @return array<int, array{date: string, time_in: ?string, time_out: ?string, total_hours: ?float}>
     */
    public function attendanceForRange(Carbon|string $start, Carbon|string $end): array
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();

        $checkins = $this->checkins()
            ->whereBetween('captured_at', [$start, $end])
            ->orderBy('captured_at')
            ->get()
            ->groupBy(fn (Checkin $checkin) => $checkin->captured_at->toDateString());

        $attendance = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateKey = $current->toDateString();
            $dayCheckins = $checkins->get($dateKey, collect());
            $attendance[] = self::computeAttendance($current, $dayCheckins);
            $current->addDay();
        }

        return $attendance;
    }

    /**
     * Compute attendance record from a collection of checkins for a single day.
     *
     * @return array{date: string, time_in: ?string, time_out: ?string, total_hours: ?float}
     */
    public static function computeAttendance(Carbon $date, \Illuminate\Support\Collection $checkins): array
    {
        $first = $checkins->first();
        $last = $checkins->count() > 1 ? $checkins->last() : null;

        $totalHours = null;
        if ($first && $last) {
            $totalHours = round($first->captured_at->floatDiffInHours($last->captured_at), 2);
        }

        return [
            'date' => $date->toDateString(),
            'time_in' => $first?->captured_at->toTimeString(),
            'time_out' => $last?->captured_at->toTimeString(),
            'total_hours' => $totalHours,
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
