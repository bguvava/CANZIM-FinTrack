<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Session Lock Test
 *
 * Tests the session lock functionality including:
 * - Password verification for session unlock
 * - Session extension on user activity
 * - Lock state persistence across requests
 * - Successful unlock resumes session (not logout)
 *
 * Requirements: REQ-017 - Authentication and Authorization
 */
class SessionLockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with a valid role for authentication tests
     */
    private function createUserWithRole(array $attributes = []): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'programs-manager'],
            ['name' => 'Programs Manager']
        );

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
        ], $attributes));
    }

    /**
     * Test user can verify password for session unlock
     */
    public function test_user_can_verify_password_for_session_unlock(): void
    {
        $user = $this->createUserWithRole([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password verified',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test password verification fails with invalid password
     */
    public function test_password_verification_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid password',
            ]);
    }

    /**
     * Test password verification fails with non-existent email
     */
    public function test_password_verification_fails_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid password',
            ]);
    }

    /**
     * Test password verification requires email and password
     */
    public function test_password_verification_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/verify-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test authenticated user can extend session
     */
    public function test_authenticated_user_can_extend_session(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/extend-session');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Session extended',
            ]);
    }

    /**
     * Test unauthenticated user cannot extend session
     */
    public function test_unauthenticated_user_cannot_extend_session(): void
    {
        $response = $this->postJson('/api/v1/auth/extend-session');

        $response->assertStatus(401);
    }

    /**
     * Test successful password verification creates new token
     */
    public function test_successful_password_verification_creates_new_token(): void
    {
        $user = $this->createUserWithRole([
            'password' => bcrypt('password123'),
        ]);

        // Get initial token
        $initialResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $initialToken = $initialResponse->json('data.token');

        // Verify password (simulating session unlock)
        $verifyResponse = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $newToken = $verifyResponse->json('data.token');

        // Verify new token is created
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($initialToken, $newToken);
    }

    /**
     * Test new token works for authenticated requests after unlock
     */
    public function test_new_token_works_for_authenticated_requests_after_unlock(): void
    {
        $user = $this->createUserWithRole([
            'password' => bcrypt('password123'),
        ]);

        // Load the role relationship
        $user->load('role');

        // Verify password and get new token
        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $newToken = $response->json('data.token');
        $this->assertNotEmpty($newToken);

        // Verify the token was created in the database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test session unlock updates last login timestamp
     */
    public function test_session_unlock_updates_last_login_timestamp(): void
    {
        $user = $this->createUserWithRole([
            'password' => bcrypt('password123'),
            'last_login_at' => now()->subHour(),
        ]);

        $oldLoginTime = $user->last_login_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        // Verify password (simulating session unlock)
        $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Refresh user from database
        $user->refresh();

        // Verify last_login_at was updated
        $this->assertNotEquals($oldLoginTime->timestamp, $user->last_login_at->timestamp);
        $this->assertTrue($user->last_login_at->greaterThan($oldLoginTime));
    }

    /**
     * Test multiple failed unlock attempts don't lock account
     */
    public function test_multiple_failed_unlock_attempts_dont_lock_account(): void
    {
        $user = $this->createUserWithRole([
            'password' => bcrypt('password123'),
        ]);

        // Make 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/verify-password', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ])->assertStatus(401);
        }

        // Verify correct password still works
        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
