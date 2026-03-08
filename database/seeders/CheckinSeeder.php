<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CheckinSeeder extends Seeder
{
    /**
     * Fixed office coordinates (Makati CBD area).
     */
    private const LATITUDE = 14.5547;

    private const LONGITUDE = 121.0244;

    private const LOCATION_NAME = 'Makati City Hall, J.P. Rizal Street, Makati, Metro Manila';

    /**
     * Fixed morning/evening times for deterministic seeding.
     * Each entry is [morning_time, evening_time].
     * Cycles through these for variety without randomness.
     */
    private const SHIFT_TIMES = [
        ['08:00:00', '17:00:00'],
        ['07:45:00', '17:15:00'],
        ['08:05:00', '17:02:00'],
        ['07:55:00', '17:10:00'],
        ['08:10:00', '16:58:00'],
    ];

    public function run(): void
    {
        $employees = Employee::all();
        $start = Carbon::create(2026, 2, 1);
        $end = Carbon::create(2026, 2, 28);

        foreach ($employees as $employee) {
            $current = $start->copy();
            $dayIndex = 0;

            while ($current->lte($end)) {
                if ($current->isWeekday()) {
                    $times = self::SHIFT_TIMES[$dayIndex % count(self::SHIFT_TIMES)];
                    $date = $current->toDateString();

                    // Morning checkin
                    Checkin::query()->create([
                        'employee_id' => $employee->id,
                        'latitude' => self::LATITUDE,
                        'longitude' => self::LONGITUDE,
                        'location_name' => self::LOCATION_NAME,
                        'selfie_path' => 'selfies/placeholder.jpg',
                        'captured_at' => "{$date} {$times[0]}",
                    ]);

                    // Evening checkin
                    Checkin::query()->create([
                        'employee_id' => $employee->id,
                        'latitude' => self::LATITUDE,
                        'longitude' => self::LONGITUDE,
                        'location_name' => self::LOCATION_NAME,
                        'selfie_path' => 'selfies/placeholder.jpg',
                        'captured_at' => "{$date} {$times[1]}",
                    ]);

                    $dayIndex++;
                }

                $current->addDay();
            }
        }
    }
}
