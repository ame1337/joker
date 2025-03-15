<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CheckIfDisconnected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $gameId = $request->user()->player->game_id;
            if ($gameId != null && ! Str::contains($request->path(), "games/$gameId")) {
                return redirect("games/$gameId");
            }
        }
        return $next($request);
    }
}