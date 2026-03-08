<?php

use App\Models\User;

it('issues a token with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);

    $response = $this->postJson('/api/v1/auth/token', [
        'email' => $user->email,
        'password' => 'secret',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/token', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/auth/token');

    $response->assertOk()
        ->assertJson(['message' => 'Token revoked.']);

    expect($user->tokens()->count())->toBe(0);
});

it('rejects unauthenticated requests', function () {
    $this->getJson('/api/v1/employees')
        ->assertStatus(401);
});
