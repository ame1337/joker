<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Game;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_determine_highest_card_of_suit_in_players_cards_and_if_suit_is_in_cards()
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);

        $cards = [
            ['strength' => 13, 'suit' => 'hearts'],
            ['strength' => 6, 'suit' => 'hearts'],
            ['strength' => 7, 'suit' => 'hearts'],
            ['strength' => 14, 'suit' => 'spades'],
            ['strength' => 10, 'suit' => 'spades'],
        ];

        $player = $game->players[0];
        $player->update(['cards' => $cards]);

        $this->assertTrue($player->isHighestSuitInCards(['strength' => 13, 'suit' => 'hearts'], 'hearts'));
        $this->assertTrue($player->isHighestSuitInCards(['strength' => 14, 'suit' => 'spades'], 'spades'));

        $this->assertTrue($player->suitNotInCards('clubs'));
        $this->assertTrue($player->suitNotInCards('diamonds'));
        $this->assertFalse($player->suitNotInCards('hearts'));
    }

    public function test_it_can_determine_if_player_can_play_a_card_aka_cvetaoba()
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);

        $cards = [
            ['strength' => 16, 'suit' => 'black_joker'],
            ['strength' => 13, 'suit' => 'hearts'],
            ['strength' => 6, 'suit' => 'hearts'],
            ['strength' => 7, 'suit' => 'hearts'],
            ['strength' => 14, 'suit' => 'spades'],
            ['strength' => 10, 'suit' => 'spades'],
        ];

        $player = $game->players[0];
        $player->update(['cards' => $cards]);

       $this->assertTrue($player->canPlay(['strength' => 13, 'suit' => 'hearts'], [], 'bez'));

        $this->assertFalse($player->canPlay(
            ['strength' => 7, 'suit' => 'hearts'],
            [['strength' => 16, 'suit' => 'color_joker', 'action' => 'magali', 'actionsuit' => 'hearts']],
            ['strength' => 16, 'suit' => 'color_joker']
        ));

        $this->assertFalse($player->canPlay(
            ['strength' => 7, 'suit' => 'hearts'],
            [['strength' => 13, 'suit' => 'spades']],
            ['strength' => 16, 'suit' => 'color_joker']
        ));

        $this->assertTrue($player->canPlay(
            ['strength' => 10, 'suit' => 'spades'],
            [['strength' => 14, 'suit' => 'diamonds']],
            ['strength' => 8, 'suit' => 'spades']
        ));

        $this->assertFalse($player->canPlay(
            ['strength' => 7, 'suit' => 'hearts'],
            [['strength' => 14, 'suit' => 'diamonds']],
            ['strength' => 8, 'suit' => 'spades']
        ));

        $this->assertTrue($player->canPlay(
            ['strength' => 7, 'suit' => 'hearts'],
            [['strength' => 14, 'suit' => 'diamonds']],
            ['strength' => 8, 'suit' => 'clubs']
        ));

        $this->assertTrue($player->canPlay(
            ['strength' => 16, 'suit' => 'black_joker', 'action' => 'mojokra'],
            [['strength' => 16, 'suit' => 'color_joker', 'action' => 'magali', 'actionsuit' => 'hearts']],
            ['strength' => 16, 'suit' => 'color_joker']
        ));
    }

    public function test_it_can_remove_a_card()
    {
        $game = Game::factory()->create();
        $game->addPlayer($game->creator);

        $cards = [
            ['strength' => 13, 'suit' => 'hearts'],
            ['strength' => 6, 'suit' => 'hearts'],
        ];

        $player = $game->players[0];
        $player->update(['cards' => $cards]);

        $player->removeCard(['strength' => 13, 'suit' => 'hearts']);

        $this->assertFalse(in_array(['strength' => 13, 'suit' => 'hearts'], $player->cards, true));

        $player->removeCard(['strength' => 6, 'suit' => 'hearts']);

        $this->assertNull($player->cards);
    }
}
