<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Authentication Service
 *
 * Handles all authentication business logic
 */
class AuthService
{
    /**
     * Maximum allowed failed login attempts before account lockout
     */
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Time window for counting failed login attempts (in minutes)
     */
    private const FAILED_ATTEMPTS_WINDOW = 15;

    /**
     * Account lockout duration (in minutes)
     */
    private const LOCKOUT_DURATION = 15;

    /**
     * Attempt to authenticate a user
     *
     * @return array{success: bool, user?: User, token?: string, message?: string}
     */
    public function login(string $email, string $password, bool $remember, string $ipAddress): array
    {
        $user = User::where('email', $email)->with('role')->first();

        if (! $user) {
            $this->logFailedLogin($email, $ipAddress, 'User not found');

            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        // Check if account is locked
        if ($user->isLocked()) {
            $this->logFailedLogin($email, $ipAddress, 'Account locked', $user->id);

            return [
                'success' => false,
                'message' => 'Your account has been locked due to multiple failed login attempts. Please try again later.',
                'status_code' => 403,
            ];
        }

        // Check if user is active
        if (! $user->isActive()) {
            return [
                'success' => false,
                'message' => 'Your account is inactive. Please contact the administrator.',
                'status_code' => 403,
            ];
        }

        // Verify password
        if (! $user->verifyPassword($password)) {
            $this->handleFailedLogin($user, $ipAddress);

            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        // Successful login
        $user->resetFailedLoginAttempts();
        $user->updateLastLogin($ipAddress);

        // Create Sanctum token
        $tokenExpiry = $remember ? 43200 : 300; // 30 days or 5 minutes
        $token = $user->createToken(
            'auth-token',
            ['*'],
            now()->addMinutes($tokenExpiry)
        )->plainTextToken;

        // Log successful login
        AuditTrail::log('login', $user, null, [
            'ip_address' => $ipAddress,
            'remember' => $remember,
        ], $user->id);

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Handle failed login attempt
     */
    private function handleFailedLogin(User $user, string $ipAddress): void
    {
        $user->incrementFailedLoginAttempts();

        // Check if should lock account
        $recentFailedAttempts = $this->getRecentFailedAttempts($user);

        if ($recentFailedAttempts >= self::MAX_FAILED_ATTEMPTS) {
            $user->lockAccount(self::LOCKOUT_DURATION);
            $this->logFailedLogin($user->email, $ipAddress, 'Account locked due to failed attempts', $user->id);
        } else {
            $this->logFailedLogin($user->email, $ipAddress, 'Invalid password', $user->id);
        }
    }

    /**
     * Get count of recent failed login attempts
     */
    private function getRecentFailedAttempts(User $user): int
    {
        if (! $user->last_failed_login_at) {
            return $user->failed_login_attempts;
        }

        $windowStart = now()->subMinutes(self::FAILED_ATTEMPTS_WINDOW);

        if ($user->last_failed_login_at->greaterThan($windowStart)) {
            return $user->failed_login_attempts;
        }

        return 0;
    }

    /**
     * Log failed login attempt
     */
    private function logFailedLogin(string $email, string $ipAddress, string $reason, ?int $userId = null): void
    {
        AuditTrail::create([
            'user_id' => $userId,
            'action' => 'failed_login',
            'auditable_type' => User::class,
            'auditable_id' => $userId ?? 0,
            'old_values' => null,
            'new_values' => [
                'email' => $email,
                'ip_address' => $ipAddress,
                'reason' => $reason,
            ],
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Logout user by revoking tokens
     */
    public function logout(User $user, string $ipAddress): void
    {
        // Revoke all tokens
        $user->tokens()->delete();

        // Log logout
        AuditTrail::log('logout', $user, null, [
            'ip_address' => $ipAddress,
        ], $user->id);
    }

    /**
     * Send password reset link
     *
     * @return array{success: bool, message: string}
     */
    public function sendPasswordResetLink(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Don't reveal if user exists
            return [
                'success' => true,
                'message' => 'If an account exists with that email, a password reset link has been sent.',
            ];
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            AuditTrail::log('password_reset_requested', $user, null, [
                'email' => $email,
            ], $user->id);

            return [
                'success' => true,
                'message' => 'If an account exists with that email, a password reset link has been sent.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Unable to send password reset link. Please try again.',
        ];
    }

    /**
     * Reset user password
     *
     * @return array{success: bool, message: string}
     */
    public function resetPassword(string $email, string $password, string $token): array
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'token' => $token],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing tokens
                $user->tokens()->delete();

                // Log password reset
                AuditTrail::log('password_reset', $user, null, [
                    'email' => $user->email,
                ], $user->id);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return [
                'success' => true,
                'message' => 'Your password has been reset successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid or expired reset token.',
        ];
    }

    /**
     * Verify if a token is valid
     */
    public function verifyToken(User $user): bool
    {
        return $user->currentAccessToken() !== null;
    }
}
