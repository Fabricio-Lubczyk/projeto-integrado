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
    Route::delete('/jogos/{game}', [AdminGameController::class, 'destroy'])->whereNumber('game')->name('games.destroy');
});

require __DIR__.'/auth.php';
