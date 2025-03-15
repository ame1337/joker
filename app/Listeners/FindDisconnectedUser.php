<?php

namespace App\Listeners;

use Laravel\Reverb\Events\ChannelRemoved;
use App\Jobs\PlayerBotJob;
use App\Jobs\WShelperJob;
use App\Models\User;

class FindDisconnectedUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ChannelRemoved $event): void
    {
        $channel_name = $event->channel->name();
        $needle = 'presence-user.';
        if (str_starts_with($channel_name, $needle)) {
            $user_id = (int) str_replace($needle, '', $channel_name);
            $user = User::find($user_id);
            $player = $user->player;
            $game = $player->game;

            if ($game === null) {
                return;
            }

            if ($game->state === 'start' || $game->state === 'ready') {
                $player->update(['game_id' => null, 'position' => null]);
                $game->refresh();
                if ($user->is($game->creator) && $game->players->count() > 0) {
                    $game->update(['user_id' => $game->players[0]->user->id]);
                    $game->reposition();

                    WShelperJob::dispatch(false, $game->id, $user->username, 'Left', $game->players, $game->user_id);
                } elseif ($game->players->count() == 0) {
                    $game->delete();
                } else {
                    $game->reposition();
                    WShelperJob::dispatch(false, $game->id, $user->username, 'Left', $game->players);
                }
                WShelperJob::dispatch(true);
            } else {
                if ($game->players()->where('disconnected', true)->count() == 3) {
                    $game->delete();
                    return;
                }
                // this one is complicated because of timings
                $player->update(['disconnected' => true]);
                WShelperJob::dispatch(false, $game->id, $user->username, 'Left');

                if ($game->turn == $player->position) {
                    PlayerBotJob::dispatch($game->players[$game->turn], $game)->delay(now()->addSeconds(5));
                }
            }
        }
    }
}
