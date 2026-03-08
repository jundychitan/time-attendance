<?php

use App\Models\Checkin;
use App\Models\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('lists employees with pagination', function () {
    Employee::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/employees');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['id', 'id_number', 'first_name', 'last_name', 'full_name']]]);
});

it('searches employees by name', function () {
    Employee::factory()->create(['first_name' => 'Alice']);
    Employee::factory()->create(['first_name' => 'Bob']);

    $response = $this->getJson('/api/v1/employees?search=Alice');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('creates an employee', function () {
    $response = $this->postJson('/api/v1/employees', [
        'id_number' => 'EMP-100',
        'first_name' => 'New',
        'last_name' => 'Employee',
        'department' => 'IT',
        'position' => 'Developer',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.id_number', 'EMP-100');

    $this->assertDatabaseHas('employees', ['id_number' => 'EMP-100']);
});

it('validates required fields on create', function () {
    $response = $this->postJson('/api/v1/employees', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['id_number', 'first_name', 'last_name']);
});

it('prevents duplicate id_number', function () {
    Employee::factory()->create(['id_number' => 'EMP-001']);

    $response = $this->postJson('/api/v1/employees', [
        'id_number' => 'EMP-001',
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('id_number');
});

it('shows an employee', function () {
    $employee = Employee::factory()->create();

    $response = $this->getJson("/api/v1/employees/{$employee->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $employee->id);
});

it('updates an employee', function () {
    $employee = Employee::factory()->create();

    $response = $this->putJson("/api/v1/employees/{$employee->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.first_name', 'Updated');
});

it('deactivates an employee on delete', function () {
    $employee = Employee::factory()->create(['is_active' => true]);

    $response = $this->deleteJson("/api/v1/employees/{$employee->id}");

    $response->assertOk();
    expect($employee->fresh()->is_active)->toBeFalse();
});

it('returns attendance for an employee', function () {
    $employee = Employee::factory()->create();

    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 08:00:00',
    ]);
    Checkin::factory()->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 17:00:00',
    ]);

    $response = $this->getJson("/api/v1/employees/{$employee->id}/attendance?start_date=2026-02-10&end_date=2026-02-10");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.time_in', '08:00:00')
        ->assertJsonPath('data.0.time_out', '17:00:00');
});
