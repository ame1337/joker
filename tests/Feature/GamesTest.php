<?php

namespace Tests\Feature;

use App\Events\GetReadyEvent;
use App\Events\UpdateLobbyEvent;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GamesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_guests_cannot_create_games()
    {
        $this->post('/games')->assertRedirect('/login');
    }

    public function test_guests_cannot_join_games()
    {
        $game = Game::factory()->create();

        $this->get($game->path())->assertRedirect('/login');
    }

    public function test_authenticated_users_can_create_games_and_the_creator_is_added_as_a_player_0()
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

    public function test_game_can_be_started_only_by_the_creator()
    {
        $game = Game::factory()->create();
        $user = $this->signIn();

        $game->addPlayer($game->creator);
        $game->addPlayer($user);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());

        $this->post('/start' . $game->path())->assertStatus(403);

        $this->signIn($game->creator);

        Event::fake();

        $this->post('/start' . $game->path());

        Event::assertDispatched(GetReadyEvent::class);
    }

    public function test_game_cannot_be_started_until_4_players()
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);
        $this->signIn($game->creator);

        $this->post('/start' . $game->path())->assertStatus(406);
    }

    public function test_a_player_can_choose_trump()
    {
        $game = Game::factory()->create(['type' => 9, 'state' => 'trump']);
        $game->addPlayer($game->creator);
        $this->signIn($game->creator);

        Event::fake();
        $this->postJson('trump' . $game->path(), ['trump' => 'hearts'])->assertStatus(200);

        $this->assertEquals(['strength' => 14, 'suit' => 'hearts'], $game->fresh()->trump);
    }

    public function test_authenticated_users_can_join_games()
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['user_id' => $user->id, 'state' => 'start']);
        $game->addPlayer($user);

        $this->signIn();
        Event::fake();

        $this->postJson('join' . $game->path())->assertStatus(200);

        Event::assertDispatched(UpdateLobbyEvent::class);
    }

    public function test_a_player_can_call()
    {
        $game = Game::factory()->create(['state' => 'call', 'quarter' => 1]);
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());

        $game->players->each(function ($player) use ($game) {
            $game->scores()->create([
                'player_id' => $player->id,
                'position' => $player->position
            ]);
        });

        $this->signIn($game->creator);

        $this->postJson('/call' . $game->path(), ['call' => 1])->assertStatus(200);
        $this->assertEquals(1, $game->players[0]->scores[0]->getData('call', 1, 1));
    }

    public function test_a_player_can_play_a_card()
    {
        $game = Game::factory()->create(['state' => 'card']);
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());

        $this->signIn($game->creator);

        $game->creator->player->update(['cards' => [['strength' => 9, 'suit' => 'hearts']]]);
        Event::fake();
        $this->postJson('/card' . $game->path(), ['card' => ['strength' => 9, 'suit' => 'hearts']])->assertOk();
    }

    public function test_check_take_updates_turn_if_not_4_cards()
    {
        $user = $this->signIn();

        $game = Game::factory()->create(['user_id' => $user->id, 'cards' => [['strength' => 14, 'suit' => 'hearts']]]);
        $game->refresh();
        Event::fake();
        $this->assertEquals(0, $game->turn);

        $game->checkTake();

        $this->assertEquals(1, $game->fresh()->turn);
    }

    public function test_calculates_scores_after_each_hand()
    {
        $game = Game::factory()->create(['type' => 9]);
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->refresh();

        $game->players->each(function ($player) use ($game) {
            $game->scores()->create([
                'player_id' => $player->id,
                'position' => $player->position
            ]);
        });

        $game->scores[0]->createCall(1, 9);
        $game->scores[1]->createCall(1, 0);
        $game->scores[2]->createCall(1, 0);
        $game->scores[3]->createCall(1, 2);

        for ($i = 0; $i < 9; $i++) {
            $game->scores[0]->incrementTake(1);
        }

        $game->calcScoresAfterHand();

        $this->assertEquals(900, $game->fresh()->scores[0]->getData('result', 1, 1));
        $this->assertEquals(50, $game->fresh()->scores[1]->getData('result', 1, 1));
        $this->assertEquals(50, $game->fresh()->scores[2]->getData('result', 1, 1));
        $this->assertEquals(-200, $game->fresh()->scores[3]->getData('result', 1, 1));
    }

    public function test_admin_can_deal_specific_cards_for_next_deal()
    {
        $admin = User::factory()->create(['email' => 'admin@joker.local']);

        $game = Game::factory()->create(['type' => 9, 'state' => 'card']);
        $game->addPlayer($game->creator);
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());
        $game->addPlayer(User::factory()->create());

        $this->signIn($admin);

        $this->postJson('/admin/cards/games/1', [
            'position' => 1,
            'cards' => [['strength' => 16, 'suit' => 'black_joker'], ['strength' => 14, 'suit' => 'hearts']]
        ])->assertOK();

        Event::fake();
        $game->refresh();

        $game->deal();

        $this->assertContains(['strength' => 16, 'suit' => 'black_joker'], $game->players[1]->cards);
        $this->assertContains(['strength' => 14, 'suit' => 'hearts'], $game->players[1]->cards);
    }
}
