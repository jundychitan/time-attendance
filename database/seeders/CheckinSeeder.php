<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $start = Carbon::create(2026, 2, 1);
        $end = Carbon::create(2026, 2, 28);

        foreach ($employees as $employee) {
            $current = $start->copy();

            while ($current->lte($end)) {
                // Skip weekends
                if ($current->isWeekday()) {
                    // Morning checkin
                    Checkin::factory()
                        ->morning($current->toDateString())
                        ->create(['employee_id' => $employee->id]);

                    // Evening checkin
                    Checkin::factory()
                        ->evening($current->toDateString())
                        ->create(['employee_id' => $employee->id]);
                }

                $current->addDay();
            }
        }
    }
}
