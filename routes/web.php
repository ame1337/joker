<?php

use App\Http\Controllers\Auth\SocialiteLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactMeController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AdminsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login/{provider}', [SocialiteLoginController::class, 'redirectToProvider'])->where('provider','facebook|google');
    
    Route::get('login/{provider}/callback', [SocialiteLoginController::class, 'handleProviderCallback'])->where('provider','facebook|google'); 
});

Route::view('/', 'home')->name('home')->middleware('disconnected');
Route::get('/contact', [ContactMeController::class, 'index']);
Route::post('/contact', [ContactMeController::class, 'store'])->name('contact');
Route::get('/user/{user}', [UsersController::class, 'show']);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/lobby', [GamesController::class, 'index'])->name('lobby')->middleware('verified');
    Route::post('/admin/start/games/{game}', [AdminsController::class, 'start']);
    Route::post('/admin/cards/games/{game}', [AdminsController::class, 'cards']);
    Route::post('/admin/addbot/games/{game}', [AdminsController::class, 'addbot']);
});

Route::group(['middleware' => ['auth', 'disconnected']], function () {
    Route::get('/games/{game}', [GamesController::class, 'show'])->middleware('verified');
    Route::post('/games', [GamesController::class, 'store'])->middleware('verified');
    Route::post('/join/games/{game}', [GamesController::class, 'join']);
    Route::post('/start/games/{game}', [GamesController::class, 'start']);
    Route::post('/ready/games/{game}', [GamesController::class, 'ready']);
    Route::post('/call/games/{game}', [GamesController::class, 'call']);
    Route::post('/card/games/{game}', [GamesController::class, 'card']);
    Route::post('/trump/games/{game}', [GamesController::class, 'trump']);
    Route::post('/kick/games/{game}', [GamesController::class, 'kick']);
    Route::post('/leave/games/{game}', [GamesController::class, 'leave']);
    Route::post('/bot/games/{game}', [GamesController::class, 'bot']);
    Route::post('/message/games/{game}', [GamesController::class, 'message'])->middleware('throttle:15,1');
});

Route::get('/dashboard', function () {
    return redirect('lobby');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
