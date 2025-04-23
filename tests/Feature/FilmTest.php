<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_create_film_with_admin()
    {
        $admin = User::factory()->create(['role_id' => 2]); 
        Sanctum::actingAs($admin);

        $fakeMovie = [
            'title' => 'Test Film',
            'release_year' => 2024,
            'length' => 120,
            'description' => 'Un film de test.',
            'rating' => 'PG',
            'special_features' => 'Interviews',
            'image' => 'testfilm.jpg',
            'language_id' => 1, 
        ];
        

        $response = $this->postJson('/api/films', $fakeMovie);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Film']);
    }

    public function test_non_admin_cannot_create_film()
    {
        $user = User::factory()->create(['role_id' => 1]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/films', [
            'title' => 'Test Film',
            'release_year' => 2024,
            'length' => 120,
            'description' => 'Un film de test.',
            'rating' => 'PG',
            'special_features' => 'Interviews',
            'image' => 'testfilm.jpg',
            'language_id' => 1, 
        ]);
        

        $response->assertStatus(403);
    }

    public function test_film_creation_has_throttle()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        Sanctum::actingAs($admin);

        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/films', [
                'title' => 'Test Film',
                'release_year' => 2024,
                'length' => 120,
                'description' => 'Un film de test.',
                'rating' => 'PG',
                'special_features' => 'Making of, Interviews',
                'image' => 'testfilm.jpg',
                'language_id' => 1
            ]);
        }

        $response = $this->postJson('/api/films', [
            'title' => 'Test Film',
            'release_year' => 2024,
            'length' => 120,
            'description' => 'Un film de test.',
            'rating' => 'PG',
            'special_features' => 'Making of, Interviews',
            'image' => 'testfilm.jpg',
            'language_id' => 1
        ]);
        

        $response->assertStatus(429);
    }

}
