<?php

namespace App\Http\Controllers;

use App\Events\CardDealEvent;
use App\Events\CardPlayEvent;
use App\Events\ChatMessageEvent;
use App\Events\PlayerCallEvent;
use App\Events\GetReadyEvent;
use App\Events\PlayerJoinLeaveEvent;
use App\Events\PlayerKickedEvent;
use App\Events\UpdateLobbyEvent;
use App\Events\UpdateReadyEvent;
use App\Events\UpdateTrumpEvent;
use App\Models\Game;
use App\Jobs\PlayerBotJob;
use App\Jobs\ResetGameStartJob;
use App\Models\PlayerBot;
use App\Rules\CardRule;
use App\Rules\GamePasswordRule;
use App\Rules\KickUserRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// bad design move front-end calls to websockets and reinvent botplay...
class GamesController extends Controller
{
    /**
     * @param Game $game
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function start(Game $game)
    {
        Gate::authorize('start', $game);

        if (! ($game->players->count() === 4)) {
            abort(406, 'Not enough players');
        }

        $game->update(['state' => 'ready', 'ready' => ['players' => [auth()->id()], 'count' => 1]]);

        ResetGameStartJob::dispatch($game)->delay(now()->addSeconds(12));

        broadcast(new GetReadyEvent($game->id, auth()->user()->player->position, '1'))->toOthers();

        return response([], 200);
    }

    public function ready(Request $request, Game $game)
    {
        Gate::authorize('ready', $game);

        $request->validate(['ready' => ['required', 'boolean']]);

        $game->addReadyPlayer(auth()->id(), $request->ready);

        broadcast(new UpdateReadyEvent($game->id, auth()->user()->player->position, $request->ready))->toOthers();

        if ($game->ready['count'] == 4) {
            $game->start();
        }

        return response([], 200);
    }

    /**
     * @param Request $request
     * @param Game $game
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function trump(Request $request, Game $game)
    {
        Gate::authorize('trump', $game);

        $request->validate([
            'trump' => ['required', 'in:hearts,clubs,diamonds,spades,bez']
        ]);

        $strength = $request->trump == 'bez' ? 16 : 14;
        $suit = $request->trump == 'bez' ? 'black_joker' : $request->trump;

        $trump = ['suit' => $suit, 'strength' => $strength];

        $game->update([
            'state' => 'call',
            'trump' => $trump
        ]);

        broadcast(new UpdateTrumpEvent($game->id, $trump));

        $game->players->each(function ($player) {
            broadcast(new CardDealEvent($player->user_id, $player->cards));
        });


        return response([], 200);
    }

    /**
     * @param Request $request
     * @param Game $game
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function call(Request $request, Game $game)
    {
        Gate::authorize('call', $game);

        $max = $game->numCardsToDeal();
        $except = $game->exceptCall();

        $request->validate([
            'call' => ['required', 'integer', 'min:0', "max:$max", "not_in:$except"]
        ]);

        $player = auth()->user()->player;

        $score = $game->scores[$player->position]->createCall($game->quarter, (int) $request->call);

        $game->updateTurn();
        $game->updateCallCount();

        broadcast(new PlayerCallEvent($game, $score, $player->position));

        if ($game->players[$game->turn]->disconnected) {
            PlayerBotJob::dispatch($game->players[$game->turn], $game)->delay(now()->addSeconds(2));
        }

        return response([], 200);

    }

    /**
     * @param Request $request
     * @param Game $game
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function card(Request $request, Game $game)
    {
        Gate::authorize('card', $game);

        $request->validate([
            'card' => ['required', 'array', new CardRule],
            'action' => ['required_if:card.strength,16', 'in:magali,caigos,mojokra,kvevidan'],
            'actionsuit' => ['required_if:action,magali,caigos', 'in:hearts,clubs,diamonds,spades']
        ]);

        $card = $request->card;

        if ($request->has('action')) {
            $card['action'] = $request->action;
            if ($request->action === 'magali' || $request->action === 'caigos') {
                $card['actionsuit'] = $request->actionsuit;
            }
        }

        $player = auth()->user()->player;

        if (! $player->canPlay($card, $game->cards, $game->trump)) {
            abort(422, "Don't bother, fool!");
        }

        $player->removeCard($request->card);

        $card = $game->addCard($card, $player);
        $checkTake = $game->checkTake();

        broadcast (new CardPlayEvent($game->id, $player->position, $card, $checkTake))->toOthers();

        if ($checkTake !== false) {
            if ($game->checkEndOfTheHand()) {
                $game->finishGame();
                return response([], 200);
            }
        }

        if ($game->players[$game->turn]->disconnected) {
            PlayerBotJob::dispatch($game->players[$game->turn], $game)->delay(now()->addSeconds(2));
        }

        return response([], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $games = Game::latest()->where('state', 'start')->get();

        return view('lobby', compact('games'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:1,9'],
            'penalty' => ['required', 'in:-200,-300,-400,-500,-900,-1000'],
            'password' => ['sometimes', 'accepted']
        ]);

        $password = $request->has('password') ? sprintf("%04d", rand(1000, 9999)) : null;

        $game = Game::create([
            'type' => $request->type,
            'penalty' => $request->penalty,
            'user_id' => auth()->id(),
            'password' => $password
        ]);

        $game->addPlayer(auth()->user());

        broadcast(new UpdateLobbyEvent());
        return redirect($game->path());
    }

    /**
     * Display the specified resource.
     *
     * @param Game $game
     */
    public function show(Request $request, Game $game)
    {
        if ($game->state === 'finished') abort(404);

        $pin = null;

        if ($game->password != null) {
            if ($request->has('pin')) {
                $pin = $request->pin;
            } else if ($game->players->contains(auth()->user()->player)) {
                $pin = $game->password;
            }
        }

        return view('game', [
            'id' => $game->id,
            'password' => (bool) $game->password,
            'pin' => $pin,
            'bot_timer' => env('BOT_TIMER'),
            'bot_disabled' => env('BOT_DISABLED')
        ]);
    }

    public function join(Request $request, Game $game)
    {
        // account for refreshs, delay joining for 3 seconds
        // related to disconnection logic in websockets handler
        sleep(3);
        $game->refresh();
        Gate::authorize('join', $game);

        $game->makeVisible('password');

        $player = auth()->user()->player;
        $cards = $player->cards;
        $cards = $game->state == 'trump' ? array_slice($cards, 0, 3) : $cards;

        if ($game->players->contains($player)) {
            if ($player->disconnected) {
                $player->update(['disconnected' => false]);
            }

            broadcast(new PlayerJoinLeaveEvent($game->id, auth()->user()->username, 'Joined', $game->players))->toOthers();

            return compact('game', 'cards');
        }

        if ($game->password != null) {
            $request->validate([
                'pin' => ['required', new GamePasswordRule($game->password)]
            ]);
        }

        if ($game->state == 'start' && $game->players()->count() < 4) {

            $game->addPlayer(auth()->user());

            $game->refresh();

            broadcast(new PlayerJoinLeaveEvent($game->id, auth()->user()->username, 'Joined', $game->players))->toOthers();
            broadcast(new UpdateLobbyEvent());

            return compact('game', 'cards');

        } else {
            return response(['message' => 'You can not join this table'], 409);
        }
    }

    public function kick(Request $request, Game $game) {
        Gate::authorize('kick', $game);

        $request->validate([
            'position' => ['required', new KickUserRule($game->players)]
        ]);

        $username = $game->players[$request->position]->username;

        $game->kick($request->position);

        broadcast(new PlayerKickedEvent($game->id, $username, $game->players));
        broadcast(new UpdateLobbyEvent());
        
        return response(["status" => "OK"], 200);
    }

    public function message(Request $request, Game $game)
    {
        Gate::authorize('message', $game);

        $request->validate([
            'message' => ['required', 'string']
        ]);

        $message = ['username' => auth()->user()->username, 'message' => $request->message];

        broadcast(new ChatMessageEvent($game->id, $message))->toOthers();
    }

    public function bot(Game $game)
    {
        Gate::authorize('bot', $game);

        $bot = new PlayerBot(auth()->user()->player, $game);
        $method = $game->state;
        $bot->$method();

        return response(["status" => "OK"], 200);
    }
}
