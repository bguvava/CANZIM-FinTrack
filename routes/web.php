<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password reset route (for email links)
Route::get('/password/reset/{token}', function () {
    return view('welcome');
})->name('password.reset');

// Dashboard route (requires authentication - handled by Vue/Sanctum)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
