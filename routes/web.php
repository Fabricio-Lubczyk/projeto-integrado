<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

Route::get('/conta', [AccountController::class, 'show'])
    ->middleware('auth')
    ->name('account.show');

Route::get('/admin', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.index');

require __DIR__.'/auth.php';
