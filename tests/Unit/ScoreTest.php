<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Score;
use App\Models\User;

class ScoreTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_can_create_call()
    {
        $score = Score::factory()->create();
        $score->refresh();

        $score->createCall(1, 1);

        $this->assertEquals(1, $score->data['q_1'][0]['call']);
    }

    public function test_it_can_get_call()
    {
        $score = Score::factory()->create();
        $score->refresh();

        $score->createCall(1, 1);

        $this->assertEquals(1, $score->getData('call', 1, 1));
    }

    public function test_it_can_increment_take()
    {
        $score = Score::factory()->create();
        $score->refresh();

        $score->createCall(1, 1);

        $this->assertEquals(0, $score->getData('take', 1, 1));
        $score->incrementTake(1);
        $this->assertEquals(1, $score->getData('take', 1, 1));
    }

    public function test_game_gets_except_attribute_correctly_from_scores()
    {
        $score = Score::factory()->create();
        $score->refresh();
        $score->createCall(1, 3);
        $game = $score->game;
        $game->addPlayer($user = User::factory()->create());
        $game->scores()->create(['player_id' => $user->player->id, 'position' => 1]);
        $score2 = Score::find(2);
        $score2->createCall(1, 3);
        $game->refresh();
        $game->type = 9;
        $game->call_count = 3;
        $this->assertEquals(3, $game->except);
    }

    public function test_it_can_update_data()
    {
        $score = Score::factory()->create();
        $score->refresh();

        $score->createCall(1, 3);

        $score->incrementTake(1);
        $score->incrementTake(1);
        $score->calcHandResult(1, 9, -500);
        $score->updateColor(1, 'y', 20);
        $score->createCall(1, 1);
        $score->incrementTake(1);
        $score->calcHandResult(1, 9, -500);
        $this->assertEquals(100, $score->fresh()->maxResult(1));
    }
}
