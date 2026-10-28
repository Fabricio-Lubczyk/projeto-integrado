<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

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
    Route::get('/jogos/novo', [AdminGameController::class, 'create'])->name('games.create');
    Route::post('/jogos', [AdminGameController::class, 'store'])->name('games.store');
});

require __DIR__.'/auth.php';
