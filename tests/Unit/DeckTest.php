<?php

namespace Tests\Unit;

use App\Models\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    public function test_deck_is_36_cards()
    {
        $deck = new Deck;

        $this->assertCount(36, $deck->cards);
    }

    public function test_it_deals_cards()
    {
        $deck = new Deck;

        $this->assertCount(4, $deck->deal(4));
    }

    public function test_it_can_determine_last_players_position()
    {
        $deck = new Deck;

        $lastPlayer = $deck->lastPlayer();

        $this->assertTrue(in_array($lastPlayer['pos'], [0, 1, 2, 3], true));
        $this->assertIsArray($lastPlayer['cards']);
    }
}
