<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

Route::get('/conta', [AccountController::class, 'show'])
    ->middleware('auth')
    ->name('account.show');

require __DIR__.'/auth.php';
