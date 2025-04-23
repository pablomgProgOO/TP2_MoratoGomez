<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Film;
use App\Models\Critic;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CriticTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); 
    }

    public function test_user_can_create_critic()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $film = Film::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 9,
            'comment' => 'Très bon film.'
        ]);

        $response->assertStatus(201);
    }

    public function test_user_cannot_critic_same_film_twice()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $film = Film::factory()->create();
        Sanctum::actingAs($user);

        Critic::factory()->create([
            'film_id' => $film->id,
            'user_id' => $user->id
        ]);

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 5,
            'comment' => 'Encore une critique.'
        ]);

        $response->assertStatus(403);
    }
    public function test_critic_creation_has_throttle()
{
    $user = User::factory()->create(['role_id' => 1]);
    $film = \App\Models\Film::factory()->create();
    Sanctum::actingAs($user);

    for ($i = 0; $i < 60; $i++) {
        $this->postJson("/api/films/{$film->id}/critics", [
            'score' => rand(1, 10),
        ]);
    }

    $response = $this->postJson("/api/films/{$film->id}/critics", [
        'score' => 10,
    ]);

    $response->assertStatus(429);
}

}
