<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\ActivityLog;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authentication Controller
 *
 * Handles user authentication, password reset, and logout
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            remember: $request->validated('remember', false),
            ipAddress: $request->ip()
        );

        if (! $result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], $result['status_code'] ?? 401);
        }

        ActivityLog::log($result['user']->id, 'user_login', 'User logged in: '.$result['user']->email, [
            'user_id' => $result['user']->id,
            'email' => $result['user']->email,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'phone_number' => $result['user']->phone_number,
                    'role' => $result['user']->role ? [
                        'id' => $result['user']->role->id,
                        'name' => $result['user']->role->name,
                        'slug' => $result['user']->role->slug,
                    ] : null,
                    'last_login_at' => $result['user']->last_login_at?->toIso8601String(),
                ],
                'token' => $result['token'],
            ],
        ], 200);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authenticated',
            ], 401);
        }

        ActivityLog::log($user->id, 'user_logout', 'User logged out: '.$user->email, [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
        ]);

        $this->authService->logout($user, $request->ip());

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->sendPasswordResetLink(
            $request->validated('email')
        );

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Reset user password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword(
            email: $request->validated('email'),
            password: $request->validated('password'),
            token: $request->validated('token')
        );

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role ? [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                        'slug' => $user->role->slug,
                    ] : null,
                    'last_login_at' => $user->last_login_at?->toIso8601String(),
                    'created_at' => $user->created_at->toIso8601String(),
                ],
            ],
        ], 200);
    }

    /**
     * Verify password for session unlock
     * This is a public route that verifies credentials without requiring a valid session
     */
    public function verifyPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login(
            email: $request->input('email'),
            password: $request->input('password'),
            remember: false,
            ipAddress: $request->ip()
        );

        if (! $result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Password verified',
            'data' => [
                'token' => $result['token'],
            ],
        ], 200);
    }

    /**
     * Extend current session
     * Called when user clicks "Stay Logged In" on warning modal
     */
    public function extendSession(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authenticated',
            ], 401);
        }

        // For Sanctum token-based auth, we just return success
        // The frontend will reset its activity timer
        return response()->json([
            'status' => 'success',
            'message' => 'Session extended',
        ], 200);
    }
}
