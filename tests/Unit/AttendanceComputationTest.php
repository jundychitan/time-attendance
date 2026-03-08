<?php

use App\Models\Checkin;
use App\Models\Employee;

it('computes time_in and time_out from first and last checkins', function () {
    $employee = Employee::factory()->create();
    $date = '2026-02-10';

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 08:00:00",
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 17:00:00",
    ]);

    $attendance = $employee->attendanceForDate($date);

    expect($attendance['date'])->toBe($date)
        ->and($attendance['time_in'])->toBe('08:00:00')
        ->and($attendance['time_out'])->toBe('17:00:00')
        ->and($attendance['total_hours'])->toBe(9.0);
});

it('returns null time_out when only one checkin exists', function () {
    $employee = Employee::factory()->create();
    $date = '2026-02-10';

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 08:30:00",
    ]);

    $attendance = $employee->attendanceForDate($date);

    expect($attendance['time_in'])->toBe('08:30:00')
        ->and($attendance['time_out'])->toBeNull()
        ->and($attendance['total_hours'])->toBeNull();
});

it('returns null values when no checkins exist for a date', function () {
    $employee = Employee::factory()->create();

    $attendance = $employee->attendanceForDate('2026-02-10');

    expect($attendance['time_in'])->toBeNull()
        ->and($attendance['time_out'])->toBeNull()
        ->and($attendance['total_hours'])->toBeNull();
});

it('computes attendance for a date range', function () {
    $employee = Employee::factory()->create();

    // Day 1: full attendance
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 08:00:00',
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 17:00:00',
    ]);

    // Day 2: no checkins (absent)

    // Day 3: partial attendance
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-12 09:00:00',
    ]);

    $attendance = $employee->attendanceForRange('2026-02-10', '2026-02-12');

    expect($attendance)->toHaveCount(3)
        ->and($attendance[0]['total_hours'])->toBe(9.0)
        ->and($attendance[1]['time_in'])->toBeNull()
        ->and($attendance[2]['time_in'])->toBe('09:00:00')
        ->and($attendance[2]['time_out'])->toBeNull();
});

it('handles multiple checkins in a day using first and last', function () {
    $employee = Employee::factory()->create();
    $date = '2026-02-10';

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 08:00:00",
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 12:00:00",
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 13:00:00",
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 18:00:00",
    ]);

    $attendance = $employee->attendanceForDate($date);

    expect($attendance['time_in'])->toBe('08:00:00')
        ->and($attendance['time_out'])->toBe('18:00:00')
        ->and($attendance['total_hours'])->toBe(10.0);
});
