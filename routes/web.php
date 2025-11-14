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

// Users route (requires authentication - Programs Manager only)
Route::get('/dashboard/users', function () {
    return view('users');
})->name('users');

// Activity Logs route (requires authentication - Programs Manager only)
Route::get('/dashboard/activity-logs', function () {
    return view('activity-logs');
})->name('activity-logs');

// Profile route (requires authentication - All roles)
Route::get('/dashboard/profile', function () {
    return view('profile');
})->name('profile');
