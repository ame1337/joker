<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckIfDisconnected;
use App\Http\Middleware\CheckLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'disconnected' => CheckIfDisconnected::class
        ]);

        $middleware->append(CheckLocale::class);

        $middleware->encryptCookies(except: [
            'lang',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
