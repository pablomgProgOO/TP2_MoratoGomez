<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Critic;
use App\Models\User;
use App\Models\Film;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([            
            LanguageSeeder::class,
            FilmSeeder::class,
            ActorSeeder::class,
            FilmActorSeeder::class,
            RoleSeeder::class
        ]);
		//Ne sera pas fait dans le cadre de ce TP, les users et les critiques seront créés par vous
        User::factory(10)->create();
        User::factory()->make([
            'login' => 'adminLog',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => 'adminPassword',
            'remember_token' => 'adminToken',
            'first_name' => 'pablo',
            'last_name' => 'mg',
            'role_id' => '2'
        ]);    
    }
}
