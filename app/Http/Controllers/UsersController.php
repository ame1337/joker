<?php

namespace App\Http\Controllers;

use App\Models\User;

class UsersController extends Controller
{

    /**
     *  show user's profile
     */
    public function show(User $user)
    {
        return view('user', compact('user'));
    }
}
