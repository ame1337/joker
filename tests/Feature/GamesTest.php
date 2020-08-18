<?php

namespace Tests\Feature;

use App\Events\GetReadyEvent;
use App\Events\UpdateLobbyEvent;
use App\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GamesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_create_games()
    {
        $this->post('/games')->assertRedirect('/login');
    }

    /** @test */
    public function guests_cannot_join_games()
    {
        $game = factory(Game::class)->create();

        $this->get($game->path())->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_can_create_games_and_the_creator_is_added_as_a_player_0()
    {
        $this->signIn();

        Event::fake();

        $response = $this->post('/games', ['type' => 1, 'penalty' => '-200']);

        $games = Game::all();

        $response->assertRedirect($games[0]->path());

        $this->assertCount(1, $games);

        $this->assertCount(1, $games[0]->players);

        $this->assertEquals(0, $games[0]->players[0]->position);

        Event::assertDispatched(UpdateLobbyEvent::class);
    }

    /** @test */
    public function game_can_be_started_only_by_the_creator()
    {
        $game = factory('App\Game')->create();
        $user = $this->signIn();

        $game->addPlayer($game->creator);
        $game->addPlayer($user);
        $game->addPlayer(factory('App\User')->create());
        $game->addPlayer(factory('App\User')->create());

        $this->post('/start' . $game->path())->assertStatus(403);

        $this->signIn($game->creator);

        Event::fake();

        $this->post('/start' . $game->path());

        Event::assertDispatched(GetReadyEvent::class);
    }

    /** @test */
    public function game_cannot_be_started_until_4_players()
    {
        $game = factory('App\Game')->create();
        $game->addPlayer($game->creator);
        $this->signIn($game->creator);

        $this->post('/start' . $game->path())->assertStatus(406);
    }

    /** @test */
    public function a_player_can_choose_trump()
    {
        $game = factory('App\Game')->create(['type' => 9, 'state' => 'trump']);
        $game->addPlayer($game->creator);
        $this->signIn($game->creator);

        Event::fake();
        $this->postJson('trump' . $game->path(), ['trump' => 'hearts'])->assertStatus(200);

        $this->assertEquals(['strength' => 14, 'suit' => 'hearts'], $game->fresh()->trump);
    }

    /** @test */
    public function authenticated_users_can_join_games()
    {
        $user = factory('App\User')->create();
        $game = factory('App\Game')->create(['user_id' => $user->id, 'state' => 'start']);
        $game->addPlayer($user);

        $this->signIn();
        Event::fake();

        $this->postJson('join' . $game->path())->assertStatus(200);

        Event::assertDispatched(UpdateLobbyEvent::class);
    }

    /** @test */
    public function a_player_can_call()
    {
        $game = factory('App\Game')->create(['state' => 'call']);
        $game->addPlayer($game->creator);
        $game->addPlayer(factory('App\User')->create());
        $game->addPlayer(factory('App\User')->create());
        $game->addPlayer(factory('App\User')->create());

        $this->signIn($game->creator);
        Event::fake();
        $this->postJson('/call' . $game->path(), ['call' => 1])->assertStatus(200);

        $this->assertDatabaseHas('scores', ['call' => 1]);
    }

    /** @test */
    public function a_player_can_play_a_card()
    {
        $game = factory('App\Game')->create(['state' => 'card']);
        $game->addPlayer($game->creator);
        $game->addPlayer(factory('App\User')->create());

        $this->signIn($game->creator);

        $game->creator->player->update(['cards' => [['strength' => 9, 'suit' => 'hearts']]]);
        Event::fake();
        $this->postJson('/card' . $game->path(), ['card' => ['strength' => 9, 'suit' => 'hearts']])->assertOk();
    }

    /** @test */
    public function check_take_updates_turn_if_not_4_cards()
    {
        $user = $this->signIn();

        $game = factory('App\Game')->create(['user_id' => $user->id, 'cards' => [['strength' => 14, 'suit' => 'hearts']]]);
        $game->refresh();
        Event::fake();
        $this->assertEquals(0, $game->turn);

        $game->checkTake();

        $this->assertEquals(1, $game->fresh()->turn);
    }

    /** @test */
    public function calculates_scores_after_each_hand()
    {
        $game = factory('App\Game')->create(['type' => 9]);
        $game->addPlayer($game->creator);
        $game->addPlayer(factory('App\User')->create());
        $game->addPlayer(factory('App\User')->create());
        $game->addPlayer(factory('App\User')->create());
        $game->refresh();

        $game->players[0]->scores()->create([
            'quarter' => 1,
            'call' => 9,
            'take' => 9
        ]);

        $game->players[1]->scores()->create([
            'quarter' => 1,
            'call' => 0,
            'take' => 0
        ]);

        $game->players[2]->scores()->create([
            'quarter' => 1,
            'call' => 0,
            'take' => 0
        ]);

        $game->players[3]->scores()->create([
            'quarter' => 1,
            'call' => 2,
            'take' => 0
        ]);

        $game->calcScoresAfterHand();

        $this->assertEquals(900, $game->fresh()->players[0]->scores[0]->result);
        $this->assertEquals(50, $game->fresh()->players[1]->scores[0]->result);
        $this->assertEquals(50, $game->fresh()->players[2]->scores[0]->result);
        $this->assertEquals(-200, $game->fresh()->players[3]->scores[0]->result);
    }
}
