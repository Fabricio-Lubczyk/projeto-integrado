<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

require __DIR__.'/auth.php';
