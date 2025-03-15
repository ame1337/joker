<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    /**
     * Determine whether the user can start a game.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function start(User $user, Game $game): bool
    {
        return $user->is($game->creator) && $game->state === 'start';
    }

    /**
     * Determine whether the user can get ready.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function ready(User $user, Game $game): bool
    {
        return $game->players->contains($user->player)
            && $user->isNot($game->creator)
            && $game->state == 'ready'
            && (! in_array($user->id, $game->ready['players'], true));
    }

    /**
     * Determine whether the user can set trump.
     *
     * @param  User  $user
     * @param  Game  $game
     * @return bool
     */
    public function trump(User $user, Game $game): bool
    {
        return $game->players->contains($user->player) && $user->player->position === $game->turn && $game->state === 'trump' && !$user->disconnected;
    }

    /**
     * Determine whether the user can play a card.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function card(User $user, Game $game): bool
    {
        return $game->players->contains($user->player) && $user->player->position === $game->turn && $game->state === 'card' && !$user->disconnected;
    }

    /**
     * Determine whether the user can call.
     *
     * @param  User  $user
     * @param  Game  $game
     * @return bool
     */
    public function call(User $user, Game $game): bool
    {
        return $game->players->contains($user->player) && $user->player->position === $game->turn && $game->state === 'call' && !$user->disconnected;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Game  $game
     * @return bool
     */
    public function leave(User $user, Game $game): bool
    {
        return $game->players->contains($user->player);
    }

    /**
     * Determine whether the user can kick a player.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function kick(User $user, Game $game): bool
    {
        return ($user->is($game->creator) || $user->isAdmin) && $game->state === 'start';
    }

    /**
     * Determine whether the user can join the game.
     *
     * @param  User  $user
     * @param  Game $game
     * @return bool
     */
    public function join(User $user, Game $game): bool
    {
        return ! in_array($user->id, $game->kicked_users);
    }

    /**
     * Determine whether the user can activate the bot.???
     *
     * @param  User  $user
     * @param Game $game
     * @return bool
     */
    public function bot(User $user, Game $game): bool
    {
        $states = ['trump', 'call', 'card'];
        return !$user->player->has_bot_kicked
            && !$user->disconnected
            && $game->players->contains($user->player)
            && $user->player->position === $game->turn 
            && in_array($game->state, $states, true);
    }

    /**
     * Determine whether the user can broadcast a message.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function message(User $user, Game $game): bool
    {
        return $game->players->contains($user->player) ||
            $game->scores->contains('player_id', $user->player->id);
    }
}
