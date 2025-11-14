<?php

namespace Tests\Feature\Authentication;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Full system access',
        ]);

        Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
            'description' => 'Financial operations',
        ]);

        Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
            'description' => 'Project implementation',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'phone_number' => '+263771234567',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'role' => ['id', 'name', 'slug'],
                        'last_login_at',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'failed_login_attempts' => 0,
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'failed_login_attempts' => 1,
        ]);
    }

    public function test_user_account_locked_after_five_failed_attempts(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        // Attempt 5 failed logins
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@canzim.org',
                'password' => 'wrongpassword',
            ]);
        }

        $user->refresh();

        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->isLocked());

        // Try to login with correct password while locked
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@canzim.org',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact the administrator.',
            ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_remember_me_creates_longer_token_expiry(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_failed_login_attempts_reset_after_successful_login(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        // Make 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@canzim.org',
                'password' => 'wrongpassword',
            ]);
        }

        $this->assertEquals(3, $user->fresh()->failed_login_attempts);

        // Successful login
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
        ]);

        $this->assertEquals(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->last_failed_login_at);
    }

    public function test_login_tracks_ip_address(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_ip);
    }

    public function test_user_can_logout(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        // Login first
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@canzim.org',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        // Logout
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);

        // Token should be revoked
        $this->assertEquals(0, $user->tokens()->count());
    }
}
