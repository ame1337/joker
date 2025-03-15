<?php

namespace App\Models;

class Deck
{
    public $cards = [
        ['strength' => 16, 'suit' => 'color_joker'], ['strength' => 16, 'suit' => 'black_joker'],
        ['strength' => 7, 'suit' => 'spades'], ['strength' => 8, 'suit' => 'spades'],
        ['strength' => 9, 'suit' => 'spades'], ['strength' => 10, 'suit' => 'spades'],
        ['strength' => 11, 'suit' => 'spades'], ['strength' => 12, 'suit' => 'spades'],
        ['strength' => 13, 'suit' => 'spades'], ['strength' => 14, 'suit' => 'spades'],
        ['strength' => 6, 'suit' => 'diamonds'], ['strength' => 7, 'suit' => 'diamonds'],
        ['strength' => 8, 'suit' => 'diamonds'], ['strength' => 9, 'suit' => 'diamonds'],
        ['strength' => 10, 'suit' => 'diamonds'], ['strength' => 11, 'suit' => 'diamonds'],
        ['strength' => 12, 'suit' => 'diamonds'], ['strength' => 13, 'suit' => 'diamonds'],
        ['strength' => 14, 'suit' => 'diamonds'], ['strength' => 7, 'suit' => 'clubs'],
        ['strength' => 8, 'suit' => 'clubs'], ['strength' => 9, 'suit' => 'clubs'],
        ['strength' => 10, 'suit' => 'clubs'], ['strength' => 11, 'suit' => 'clubs'],
        ['strength' => 12, 'suit' => 'clubs'], ['strength' => 13, 'suit' => 'clubs'],
        ['strength' => 14, 'suit' => 'clubs'], ['strength' => 6, 'suit' => 'hearts'],
        ['strength' => 7, 'suit' => 'hearts'], ['strength' => 8, 'suit' => 'hearts'],
        ['strength' => 9, 'suit' => 'hearts'], ['strength' => 10, 'suit' => 'hearts'],
        ['strength' => 11, 'suit' => 'hearts'], ['strength' => 12, 'suit' => 'hearts'],
        ['strength' => 13, 'suit' => 'hearts'], ['strength' => 14, 'suit' => 'hearts'],
    ];


    public function __construct()
    {
        shuffle($this->cards);
    }

    /**
     * @param $num
     * @return array
     */
    public function deal($num)
    {
        return array_slice($this->cards, 0, $num);
    }

    public function lastPlayer()
    {
        $deck = [];
        for ($i = 0; $i < 36; $i++) {
            if ($this->cards[$i]['strength'] === 14) {
                $deck['cards'] = array_slice($this->cards, 0, $i + 1);
                $deck['pos'] = ($i + 1) % 4;
                return $deck;
            }
        }
    }
}
