<?php

declare(strict_types=1);

/**
 * API Routes for CANZIM FinTrack
 *
 * All API routes are prefixed with /api/v1
 * All routes require authentication via Laravel Sanctum tokens
 *
 * @see https://laravel.com/docs/12.x/routing
 * @see https://laravel.com/docs/12.x/sanctum
 */

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'CANZIM FinTrack API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/profile', [AuthController::class, 'profile'])->name('api.auth.profile');
    });

    // User profile endpoint (deprecated - use /auth/profile instead)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('role'),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Module Routes (To be implemented in subsequent modules)
    |--------------------------------------------------------------------------
    |
    | The following route groups will be populated as modules are developed:
    | - Dashboard routes (/api/v1/dashboard)
    | - Project routes (/api/v1/projects)
    | - Budget routes (/api/v1/budgets)
    | - Expense routes (/api/v1/expenses)
    | - Cash flow routes (/api/v1/cash-flow)
    | - Purchase order routes (/api/v1/purchase-orders)
    | - Donor routes (/api/v1/donors)
    | - Report routes (/api/v1/reports)
    | - User management routes (/api/v1/users)
    | - Document routes (/api/v1/documents)
    |
    */
});
