<?php

use App\Models\Checkin;
use App\Models\Employee;

it('has a full_name accessor', function () {
    $employee = Employee::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($employee->full_name)->toBe('John Doe');
});

it('has many checkins', function () {
    $employee = Employee::factory()->create();
    Checkin::factory()->count(3)->create(['employee_id' => $employee->id]);

    expect($employee->checkins)->toHaveCount(3);
});

it('scopes active employees', function () {
    Employee::factory()->count(2)->create(['is_active' => true]);
    Employee::factory()->create(['is_active' => false]);

    expect(Employee::query()->active()->count())->toBe(2);
});

it('scopes search by name', function () {
    Employee::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith']);
    Employee::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones']);

    expect(Employee::query()->search('Alice')->count())->toBe(1);
});

it('scopes search by id_number', function () {
    Employee::factory()->create(['id_number' => 'EMP-999']);
    Employee::factory()->create(['id_number' => 'EMP-001']);

    expect(Employee::query()->search('EMP-999')->count())->toBe(1);
});
