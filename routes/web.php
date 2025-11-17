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

// Projects routes (requires authentication - Programs Manager, Finance Officer)
Route::get('/projects', function () {
    return view('projects.index');
})->name('projects.index');

Route::get('/projects/create', function () {
    return view('projects.create');
})->name('projects.create');

Route::get('/projects/{id}', function () {
    return view('projects.show');
})->name('projects.show');

Route::get('/projects/{id}/edit', function () {
    return view('projects.edit');
})->name('projects.edit');

// Budgets routes (requires authentication - Programs Manager, Finance Officer)
Route::get('/budgets', function () {
    return view('budgets.index');
})->name('budgets.index');

Route::get('/budgets/create', function () {
    return view('budgets.create');
})->name('budgets.create');

// Expenses routes (requires authentication - All roles with different permissions)
Route::get('/expenses', function () {
    return view('expenses.index');
})->name('expenses.index');

Route::get('/expenses/create', function () {
    return view('expenses.create');
})->name('expenses.create');

Route::get('/expenses/pending-review', function () {
    return view('expenses.pending-review');
})->name('expenses.pending-review');

Route::get('/expenses/pending-approval', function () {
    return view('expenses.pending-approval');
})->name('expenses.pending-approval');

Route::get('/expenses/{id}', function () {
    return view('expenses.show');
})->name('expenses.show');

// Cash Flow routes (requires authentication - Programs Manager, Finance Officer)
Route::get('/cash-flow', function () {
    return view('cash-flow.index');
})->name('cash-flow.index');

Route::get('/cash-flow/bank-accounts', function () {
    return view('cash-flow.bank-accounts');
})->name('cash-flow.bank-accounts');

Route::get('/cash-flow/transactions', function () {
    return view('cash-flow.transactions');
})->name('cash-flow.transactions');

Route::get('/cash-flow/projections', function () {
    return view('cash-flow.projections');
})->name('cash-flow.projections');

// Purchase Orders routes (requires authentication - Programs Manager, Finance Officer)
Route::get('/purchase-orders', function () {
    return view('purchase-orders.index');
})->name('purchase-orders.index');

Route::get('/purchase-orders/vendors', function () {
    return view('purchase-orders.vendors');
})->name('purchase-orders.vendors');

Route::get('/purchase-orders/pending-approval', function () {
    return view('purchase-orders.pending-approval');
})->name('purchase-orders.pending-approval');

Route::get('/purchase-orders/receiving', function () {
    return view('purchase-orders.receiving');
})->name('purchase-orders.receiving');

// Reports route (requires authentication - Programs Manager, Finance Officer)
Route::get('/dashboard/reports', function () {
    return view('reports');
})->name('reports');

// Documents route (requires authentication - Programs Manager, Project Officer)
Route::get('/dashboard/documents', function () {
    return view('documents');
})->name('documents');

// Settings route (requires authentication - Programs Manager only)
Route::get('/dashboard/settings', function () {
    return view('settings');
})->name('settings');

// Donors route (requires authentication - Programs Manager, Finance Officer)
Route::get('/donors', function () {
    return view('donors');
})->name('donors');
