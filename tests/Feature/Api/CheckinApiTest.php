<?php

use App\Models\Checkin;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
    Storage::fake('public');
});

it('creates a checkin with selfie upload', function () {
    $employee = Employee::factory()->create(['id_number' => 'EMP-001']);

    $response = $this->postJson('/api/v1/checkins', [
        'id_number' => 'EMP-001',
        'latitude' => 14.5547,
        'longitude' => 121.0244,
        'location_name' => 'Manila Office',
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        'captured_at' => '2026-02-10 08:00:00',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'latitude', 'longitude', 'selfie_url', 'captured_at']]);

    $this->assertDatabaseHas('checkins', [
        'employee_id' => $employee->id,
        'location_name' => 'Manila Office',
    ]);

    Storage::disk('public')->assertExists(
        Checkin::query()->first()->selfie_path
    );
});

it('validates required fields', function () {
    $response = $this->postJson('/api/v1/checkins', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['id_number', 'latitude', 'longitude', 'selfie', 'captured_at']);
});

it('validates employee exists', function () {
    $response = $this->postJson('/api/v1/checkins', [
        'id_number' => 'NONEXISTENT',
        'latitude' => 14.5,
        'longitude' => 121.0,
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        'captured_at' => '2026-02-10 08:00:00',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('id_number');
});

it('lists checkins with filters', function () {
    $employee = Employee::factory()->create();
    Checkin::factory()->count(3)->create([
        'employee_id' => $employee->id,
        'captured_at' => '2026-02-10 08:00:00',
    ]);
    Checkin::factory()->count(2)->create([
        'captured_at' => '2026-02-11 08:00:00',
    ]);

    // Filter by date
    $response = $this->getJson('/api/v1/checkins?date=2026-02-10');
    $response->assertOk()
        ->assertJsonCount(3, 'data');

    // Filter by employee
    $response = $this->getJson("/api/v1/checkins?employee_id={$employee->id}");
    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
