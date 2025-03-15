<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can update profile
     * @param User $user
     * @param User $requestUser
     * @return bool
     */
    public function update(User $user, User $requestUser): bool
    {
        return $user->id === $requestUser->id && ! $user->socialite_account;
    }
}
