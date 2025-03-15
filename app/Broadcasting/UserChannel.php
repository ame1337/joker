<?php

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  User  $user
     * @return array
     */
    public function join(User $user, $user_id)
    {
        if ($user->id === (int) $user_id) {
            return ['id' => $user->id, 'name' => $user->username];
        }
    }
}
