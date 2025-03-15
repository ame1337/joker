<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Game;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Score>
 */
class ScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);
        return [
            'player_id' => $game->creator->player->id,
            'game_id' => $game->id,
            'position' => 0
        ];
    }
}
