<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function user_registers()
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'testuser',
            'email' => 'test@example.com',
            'email_confirmation' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'message',
            'user' => [
                'login',
                'email',
                'first_name',
                'last_name'
            ]
        ]);
    }

    public function register_fails()
    {
        $response = $this->postJson('/api/signup', [
            'login' => '',
            'email' => 'not-an-email',
            'email_confirmation' => 'different@email.com',
            'password' => 'short',
            'password_confirmation' => 'diff',
            'first_name' => '',
            'last_name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'login',
            'email',
            'password',
            'password_confirmation',
            'first_name',
            'last_name',
        ]);
    }

    public function user_logins_with_correct_credentials()
    {
        $user = User::factory()->create([
            'login' => 'demo',
            'password' => bcrypt('secret123')
        ]);

        $response = $this->postJson('/api/signin', [
            'login' => 'demo',
            'password' => 'secret123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'user' => ['login', 'email', 'first_name', 'last_name'],
            'token'
        ]);
    }

    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'login' => 'demo',
            'password' => bcrypt('secret123')
        ]);

        $response = $this->postJson('/api/signin', [
            'login' => 'demo',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Login ou/et mot de passe invalide'
        ]);
    }

    public function user_logouts()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/signout');
        $response->assertNoContent();
    }

    public function logout_fails_when_unauthenticated()
    {
        $response = $this->postJson('/api/signout');
        $response->assertStatus(401);
    }

    public function register_throttles_with_5_tries()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/signup', [
                'login' => 'testuser' . $i,
                'email' => "test{$i}@example.com",
                'email_confirmation' => "test{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'first_name' => 'Test',
                'last_name' => 'User',
            ]);
        }

        $response = $this->postJson('/api/signup', [
            'login' => 'testuser6',
            'email' => "test6@example.com",
            'email_confirmation' => "test6@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $response->assertStatus(429);
    }
}
