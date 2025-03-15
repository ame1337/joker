<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Models\Game;
use App\Models\User;

class ScoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_game_starts_empty_scores_are_created()
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());

        Event::fake();
        Queue::fake();
        $game->start();

        $this->assertDatabaseCount('scores', 4);
    }

    public function test_when_user_calls_score_is_updated()
    {
        $this->withoutExceptionHandling();
        $game = Game::factory()->create(['state' => 'call']);
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());

        $this->signIn($game->creator);
        Event::fake();
        Queue::fake();

        $game->fresh()->start();

        $this->postJson('call' . $game->path(), ['call' => 1]);

        $this->assertEquals(1, $game->scores[0]->data['q_1'][0]['call']);
    }
}
