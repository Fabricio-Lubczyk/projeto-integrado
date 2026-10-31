<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');
Route::get('/jogos', [GameController::class, 'index'])->name('games.index');
Route::get('/jogos/{game}', [GameController::class, 'show'])->whereNumber('game')->name('games.show');
Route::post('/jogos/{game}/avaliacoes', [ReviewController::class, 'store'])
    ->middleware('auth')->whereNumber('game')->name('reviews.store');

Route::middleware('auth')->group(function () {
    Route::get('/avaliacoes/{review}/editar', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/avaliacoes/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/avaliacoes/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

Route::get('/conta', [AccountController::class, 'show'])
    ->middleware('auth')
    ->name('account.show');

Route::get('/conta/editar', [AccountController::class, 'edit'])->middleware('auth')->name('account.edit');

Route::patch('/conta', [AccountController::class, 'update'])->middleware('auth')->name('account.update');

Route::get('/conta/senha', [PasswordController::class, 'edit'])->middleware('auth')->name('account.password.edit');
Route::put('/conta/senha', [PasswordController::class, 'update'])->middleware(['auth', 'throttle:5,1'])->name('account.password.update');

Route::get('/admin', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.index');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/jogos', [AdminGameController::class, 'index'])->name('games.index');
    Route::get('/jogos/novo', [AdminGameController::class, 'create'])->name('games.create');
    Route::post('/jogos', [AdminGameController::class, 'store'])->name('games.store');
    Route::get('/jogos/{game}/editar', [AdminGameController::class, 'edit'])->whereNumber('game')->name('games.edit');
    Route::put('/jogos/{game}', [AdminGameController::class, 'update'])->whereNumber('game')->name('games.update');
    Route::get('/jogos/{game}/excluir', [AdminGameController::class, 'delete'])->whereNumber('game')->name('games.delete');
    Route::delete('/jogos/{game}', [AdminGameController::class, 'destroy'])->whereNumber('game')->name('games.destroy');
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/games', [App\Http\Controllers\Api\GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [App\Http\Controllers\Api\GameController::class, 'show'])->name('games.show');
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/games', [App\Http\Controllers\Api\GameController::class, 'store'])->name('games.store');
        Route::put('/games/{game}', [App\Http\Controllers\Api\GameController::class, 'update'])->name('games.update');
        Route::delete('/games/{game}', [App\Http\Controllers\Api\GameController::class, 'destroy'])->name('games.destroy');
    });
});

require __DIR__.'/auth.php';
