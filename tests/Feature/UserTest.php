<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); 
    }

    public function test_user_can_view_own_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/users/{$user->id}");
        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_user()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/users/{$other->id}");
        $response->assertStatus(403);
    }

    public function test_user_can_update_own_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/users/{$user->id}/password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Mot de passe mis à jour avec succès.']);
    }

    public function test_user_cannot_update_other_user_password()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/users/{$other->id}/password", [
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_password_update_has_throttle()
    {   
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 60; $i++) {
            $this->patchJson("/api/users/{$user->id}/password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
        }

        $response = $this->patchJson("/api/users/{$user->id}/password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(429);
    }

}
