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

it('pairs overnight shift checkin with next morning checkout', function () {
    $employee = Employee::factory()->create();

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 20:00:00',
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-11 06:00:00',
    ]);

    $attendance = $employee->attendanceForDate('2026-02-10');

    expect($attendance['time_in'])->toBe('20:00:00')
        ->and($attendance['time_out'])->toBe('06:00:00')
        ->and($attendance['time_out_next_day'])->toBeTrue()
        ->and($attendance['total_hours'])->toBe(10.0);
});

it('returns null time_out for overnight shift with no next-day checkout', function () {
    $employee = Employee::factory()->create();

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 22:00:00',
    ]);

    $attendance = $employee->attendanceForDate('2026-02-10');

    expect($attendance['time_in'])->toBe('22:00:00')
        ->and($attendance['time_out'])->toBeNull()
        ->and($attendance['total_hours'])->toBeNull();
});

it('applies 16-hour rule to discard invalid time-out', function () {
    $employee = Employee::factory()->create();
    $date = '2026-02-10';

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 06:00:00",
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 22:00:00",
    ]);

    $attendance = $employee->attendanceForDate($date);

    // 16 hours gap -> last checkin is NOT a valid time-out
    expect($attendance['time_in'])->toBe('06:00:00')
        ->and($attendance['time_out'])->toBeNull()
        ->and($attendance['total_hours'])->toBeNull();
});

it('does not double-count consumed overnight checkout in range', function () {
    $employee = Employee::factory()->create();

    // Night shift: checkin at 20:00 day 1, checkout at 06:00 day 2
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 20:00:00',
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-11 06:00:00',
    ]);

    $attendance = $employee->attendanceForRange('2026-02-10', '2026-02-11');

    // Day 1: overnight shift paired
    expect($attendance[0]['time_in'])->toBe('20:00:00')
        ->and($attendance[0]['time_out'])->toBe('06:00:00')
        ->and($attendance[0]['time_out_next_day'])->toBeTrue()
        ->and($attendance[0]['total_hours'])->toBe(10.0);

    // Day 2: consumed checkout should NOT appear as time-in
    expect($attendance[1]['time_in'])->toBeNull()
        ->and($attendance[1]['time_out'])->toBeNull();
});

it('uses manual_time_out when auto time-out is null', function () {
    $employee = Employee::factory()->create();
    $date = '2026-02-10';

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => "{$date} 20:00:00",
        'manual_time_out' => "{$date} 23:30:00",
    ]);

    $attendance = $employee->attendanceForDate($date);

    expect($attendance['time_in'])->toBe('20:00:00')
        ->and($attendance['time_out'])->toBe('23:30:00')
        ->and($attendance['manual_time_out'])->toBe('23:30:00')
        ->and($attendance['total_hours'])->toBe(3.5);
});
