<?php

namespace Tests\Feature\Authentication;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Full system access',
        ]);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@canzim.org',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_password_reset_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_reset_request_does_not_reveal_user_existence(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@canzim.org',
        ]);

        // Should return success even if user doesn't exist (security best practice)
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('oldpassword'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@canzim.org',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_reset_requires_token(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@canzim.org',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_password_reset_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'some-token',
            'email' => 'test@canzim.org',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_reset_requires_minimum_password_length(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'some-token',
            'email' => 'test@canzim.org',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('oldpassword'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@canzim.org',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400);
    }

    public function test_password_reset_revokes_all_tokens(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@canzim.org',
            'password' => Hash::make('oldpassword'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        // Create a token
        $user->createToken('test-token');
        $this->assertEquals(1, $user->tokens()->count());

        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@canzim.org',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // All tokens should be revoked
        $this->assertEquals(0, $user->tokens()->count());
    }
}
