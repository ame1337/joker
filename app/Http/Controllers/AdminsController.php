<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\PlayerJoinLeaveEvent;
use App\Events\UpdateLobbyEvent;
use Exception;

class AdminsController extends Controller
{
    public function start(Game $game)
    {
        if (! auth()->user()->isAdmin) return response([], 403);

        if (! ($game->players->count() === 4) || $game->state != 'start') {
            abort(400, '');
        }

        $game->start();
        return response(['status' => 'OK'], 200);
    }

    public function addbot(Game $game)
    {
        if (! auth()->user()->isAdmin) return response([], 403);

        if (($game->players->count() < 4) && $game->state == 'start') {
            $user = User::where('email', 'REGEXP', '@jokerbot.local$')->get()
                ->first(function($usr, $idx) {
                    return $usr->player->game_id === null;
                });
            $user->player->update(['disconnected' => true]);
            $game->addPlayer($user);

            $game->refresh();

            broadcast(new PlayerJoinLeaveEvent($game->id, $user->username, 'Joined', $game->players));
            broadcast(new UpdateLobbyEvent());

            return response(['status' => 'OK'], 200);
        } else {
            abort(400, '');
        }
    }

    public function cards(Request $request, Game $game)
    {
        if (! auth()->user()->isAdmin || ! in_array($game->state, ['trump', 'call', 'card'], true)) {
            abort(400, '');
        }

        if (count($request->cards) > ($game->numCardsToDeal() + 1) ||
            count($request->cards) === 0 ||
            $game->admin_cards !== null) {
            abort(400, '');
        }

        try {
            $validator = Validator::make($request->all(), [
                'position' => ['required', 'integer', 'min:0', 'max:3'],
                'cards' => [
                    'required',
                    'array',
                    function ($attribute, $value, $fail) {
                        $cards = [
                            ['strength' => 16, 'suit' => 'color_joker'],
                            ['strength' => 16, 'suit' => 'black_joker'],
                            ['strength' => 14, 'suit' => 'hearts'],
                            ['strength' => 14, 'suit' => 'clubs'],
                            ['strength' => 14, 'suit' => 'diamonds'],
                            ['strength' => 14, 'suit' => 'spades'],
                        ];

                        foreach($value as $card) {
                            if (! in_array($card, $cards, true)) {
                                $fail("Invalid $attribute");
                            }
                        }
                    },
                ],
            ]);

            $validator->validate();

            $game->update(['admin_cards' => [
                'position' => $request->position,
                'cards' => array_unique($request->cards, SORT_REGULAR)
            ]]);
        } catch(Exception $e) {
            abort(400, '');
        }

        return response(['status' => 'OK'], 200);
    }
}
