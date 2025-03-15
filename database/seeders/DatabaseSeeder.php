<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Gravatar;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'bot1',
            'email' => 'bot1@jokerbot.local',
            'avatar_url' => Gravatar::url('bot1@joker.local')
        ]);

        User::factory()->create([
            'username' => 'bot2',
            'email' => 'bot2@jokerbot.local',
            'avatar_url' => Gravatar::url('bot2@joker.local')
        ]);

        User::factory()->create([
            'username' => 'bot3',
            'email' => 'bot3@jokerbot.local',
            'avatar_url' => Gravatar::url('bot3@joker.local')
        ]);

        User::factory()->create([
            'username' => 'bot',
            'email' => 'bot@jokerbot.local',
            'avatar_url' => Gravatar::url('bot@joker.local')
        ]);

        User::factory()->create([
            'username' => 'user',
            'email' => 'user@joker.local',
            'avatar_url' => Gravatar::url('user@joker.local')
        ]);

        User::factory()->create([
            'username' => 'Admin',
            'email' => 'admin@joker.local',
            'avatar_url' => Gravatar::url('admin@joker.local')
        ]);

        User::factory()->create([
            'username' => 'Joker',
            'email' => 'joker@joker.local',
            'avatar_url' => Gravatar::url('joker@joker.local')
        ]);
    }
}
