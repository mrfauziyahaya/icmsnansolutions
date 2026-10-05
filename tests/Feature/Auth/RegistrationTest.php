<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * There is no public self-registration (removed 2026-10-05 after an
 * unauthenticated party used an open /register to reach /clients and
 * /quote-templates). This asserts the route stays gone rather than
 * exercising a registration flow that no longer exists.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_there_is_no_registration_route(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name'                  => 'Nobody',
            'email'                 => 'nobody@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'nobody@example.com']);
    }
}
