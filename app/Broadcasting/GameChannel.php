<?php

namespace App\Broadcasting;

use App\Models\Game;
use App\Models\User;

class GameChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  User  $user
     * @return bool
     */
    public function join(User $user, Game $game)
    {
        return $game->players->contains($user->player);
    }
}
