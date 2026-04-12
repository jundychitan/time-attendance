<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registration is disabled — only super admins can create admin users.
 * These tests are skipped.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_disabled()
    {
        // Registration routes are removed, visitors should get 404
        $response = $this->get('/register');

        $response->assertNotFound();
    }
}
