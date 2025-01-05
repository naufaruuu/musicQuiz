<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\GameController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});




// Login routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes that require authentication
Route::middleware(['auth.user'])->group(function () {
    Route::get('/searchArtist', [ArtistController::class, 'searchArtistForm'])->name('searchArtist.form');
    Route::post('/searchArtist', [ArtistController::class, 'searchArtist'])->name('searchArtist');
    Route::get('/selectArtist', [ArtistController::class, 'selectArtistForm'])->name('selectArtist.form');
    Route::post('/selectArtist', [ArtistController::class, 'selectArtist'])->name('selectArtist');
    Route::get('/game/question', [GameController::class, 'showQuestion'])->name('game.question');
    Route::get('/game/start', [GameController::class, 'startGame'])->name('game.start');
    Route::get('/game/question', [GameController::class, 'showQuestion'])->name('game.question');
    Route::post('/game/answer', [GameController::class, 'answerQuestion'])->name('game.answer');
    Route::get('/game/finish', [GameController::class, 'finishGame'])->name('game.finish');


});

