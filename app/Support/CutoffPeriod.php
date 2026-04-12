<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class CutoffPeriod
{
    public readonly Carbon $start;

    public readonly Carbon $end;

    public readonly string $label;

    public function __construct(Carbon $start, Carbon $end)
    {
        $this->start = $start->startOfDay();
        $this->end = $end->endOfDay();
        $this->label = $start->format('M j').' – '.$end->format('M j, Y');
    }

    /**
     * Get the cutoff period containing today.
     */
    public static function current(): self
    {
        return self::forDate(Carbon::today());
    }

    /**
     * Get the cutoff period containing the given date.
     */
    public static function forDate(Carbon|string $date): self
    {
        $date = Carbon::parse($date);
        $cutoffDay = config('attendance.cutoff_day', 15);

        if ($date->day <= $cutoffDay) {
            // Period 1: 1st to cutoff_day
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->setDay($cutoffDay);
        } else {
            // Period 2: cutoff_day + 1 to end of month
            $start = $date->copy()->setDay($cutoffDay + 1);
            $end = $date->copy()->endOfMonth();
        }

        return new self($start, $end);
    }

    /**
     * Get the previous cutoff period.
     */
    public function previous(): self
    {
        return self::forDate($this->start->copy()->subDay());
    }

    /**
     * Get the next cutoff period.
     */
    public function next(): self
    {
        return self::forDate($this->end->copy()->addDay());
    }

    /**
     * Convert to array for passing to frontend.
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
            'label' => $this->label,
        ];
    }
}
