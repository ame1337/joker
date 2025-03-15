<?php

namespace App\Models;

use App\Events\CardDealEvent;
use App\Events\CardPlayEvent;
use App\Events\PlayerCallEvent;
use App\Events\UpdateTrumpEvent;
use App\Jobs\PlayerBotJob;

class PlayerBot
{

    private $player, $game;

    /**
     * PlayerBot constructor.
     */
    public function __construct($player, $game)
    {
        $this->player = $player;
        $this->game = $game;
    }

    public function trump()
    {
        $trump = ['strength' => 16, 'suit' => 'black_joker'];

        $this->game->update(['state' => 'call', 'trump' => $trump]);

        broadcast(new UpdateTrumpEvent($this->game->id, $trump));

        $this->game->players->each(function ($player) {
            broadcast(new CardDealEvent($player->user_id, $player->cards));
        });

        if ($this->game->players[$this->game->turn]->disconnected) {
            PlayerBotJob::dispatch($this->game->players[$this->game->turn], $this->game)->delay(now()->addSeconds(2));
        }
    }

    public function call()
    {
        $except = $this->game->exceptCall();

        $jokCount = $this->player->jokInCards();

        if ($except != $jokCount) {
            $call = $jokCount;
        } else {
            $call = $except === 0 ? 1 : 0;
        }

        $score = $this->game->scores[$this->player->position]->createCall($this->game->quarter, $call);

        $this->game->updateTurn();
        $this->game->updateCallCount();

        broadcast(new PlayerCallEvent($this->game, $score, $this->player->position));

        if ($this->game->players[$this->game->turn]->disconnected) {
            PlayerBotJob::dispatch($this->game->players[$this->game->turn], $this->game)->delay(now()->addSeconds(2));
        }
    }

    public function card()
    {
        foreach ($this->player->cards as $card) {
            $card2 = $card;
            if (empty($this->game->cards) && $card['strength'] == 16) {
                $card2['action'] = 'magali';
                $suit = $this->game->trump['strength'] == 16 ? 'hearts' : $this->game->trump['suit'];
                $card2['actionsuit'] = $suit;
            } elseif ($card['strength'] == 16) {
                $card2['action'] = 'mojokra';
            }

            if ($this->player->canPlay($card2, $this->game->cards, $this->game->trump)) {
                $this->player->removeCard($card);

                $card = $this->game->addCard($card2, $this->player);
                $checkTake = $this->game->checkTake();

                broadcast (new CardPlayEvent($this->game->id, $this->player->position, $card, $checkTake));

                if ($checkTake !== false) {
                    if ($this->game->checkEndOfTheHand()) {
                        $this->game->finishGame();
                        return;
                    }
                }

                break;
            }
        }

        if ($this->game->players[$this->game->turn]->disconnected) {
            PlayerBotJob::dispatch($this->game->players[$this->game->turn], $this->game)->delay(now()->addSeconds(2));
        }

    }
}
