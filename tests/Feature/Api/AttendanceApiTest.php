<?php

use App\Models\Checkin;
use App\Models\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('returns daily attendance for a date', function () {
    $employee = Employee::factory()->create();
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 08:00:00',
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 17:30:00',
    ]);

    $response = $this->getJson('/api/v1/attendance?date=2026-02-10');

    $response->assertOk()
        ->assertJsonPath('data.0.time_in', '08:00:00')
        ->assertJsonPath('data.0.time_out', '17:30:00');
});

it('filters attendance by employee', function () {
    $emp1 = Employee::factory()->create();
    $emp2 = Employee::factory()->create();

    Checkin::factory()->create(['employee_id' => $emp1->id, 'captured_at' => '2026-02-10 08:00:00']);
    Checkin::factory()->create(['employee_id' => $emp2->id, 'captured_at' => '2026-02-10 08:00:00']);

    $response = $this->getJson("/api/v1/attendance?date=2026-02-10&employee_id={$emp1->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns attendance summary with stats', function () {
    $employee = Employee::factory()->create();
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => now()->setTime(8, 0)->toDateTimeString(),
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => now()->setTime(17, 0)->toDateTimeString(),
    ]);

    $response = $this->getJson('/api/v1/attendance/summary');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'date',
                'total_employees',
                'present_today',
                'absent_today',
                'average_hours',
                'recent_checkins',
            ],
        ])
        ->assertJsonPath('data.total_employees', 1)
        ->assertJsonPath('data.present_today', 1)
        ->assertJsonPath('data.absent_today', 0);
});

it('returns attendance for a date range', function () {
    $employee = Employee::factory()->create();

    Checkin::factory()->create(['employee_id' => $employee->id, 'captured_at' => '2026-02-10 08:00:00']);
    Checkin::factory()->create(['employee_id' => $employee->id, 'captured_at' => '2026-02-10 17:00:00']);
    Checkin::factory()->create(['employee_id' => $employee->id, 'captured_at' => '2026-02-11 09:00:00']);

    $response = $this->getJson('/api/v1/attendance?start_date=2026-02-10&end_date=2026-02-11');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
