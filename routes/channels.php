<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('lobby', function ($user) {
    return auth()->check();
});
Broadcast::channel('game.{game}', \App\Broadcasting\GameChannel::class);
Broadcast::channel('user.{user_id}', \App\Broadcasting\UserChannel::class);
