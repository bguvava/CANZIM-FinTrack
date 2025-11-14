<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * User Profile Management Tests
 */
class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Role $programsManagerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Manages all programs',
        ]);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->programsManagerRole->id,
            'office_location' => 'Harare Office',
            'phone_number' => '+263771234567',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_own_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $this->user->id,
                    'name' => 'Test User',
                    'email' => 'test@test.com',
                    'office_location' => 'Harare Office',
                    'phone_number' => '+263771234567',
                    'status' => 'active',
                ],
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    }

    /** @test */
    public function user_can_update_own_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'phone_number' => '+263777654321',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'phone_number' => '+263777654321',
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'profile_updated',
        ]);
    }

    /** @test */
    public function update_profile_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => '',
            'email' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /** @test */
    public function update_profile_validates_email_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function update_profile_validates_email_uniqueness_excluding_self(): void
    {
        // Create another user
        $otherUser = User::factory()->create([
            'email' => 'other@test.com',
            'role_id' => $this->programsManagerRole->id,
        ]);

        Sanctum::actingAs($this->user);

        // Try to use another user's email
        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Test User',
            'email' => 'other@test.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        // Same email should work (updating with own email)
        $response2 = $this->putJson('/api/v1/profile', [
            'name' => 'Test User',
            'email' => 'test@test.com', // Own email
        ]);

        $response2->assertOk();
    }

    /** @test */
    public function user_can_change_password(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newPassword123',
            'new_password_confirmation' => 'newPassword123',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Password changed successfully.',
            ]);

        // Verify password was changed
        $this->user->refresh();
        $this->assertTrue(password_verify('newPassword123', $this->user->password));

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'password_changed',
        ]);
    }

    /** @test */
    public function change_password_validates_current_password(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'wrongPassword',
            'new_password' => 'newPassword123',
            'new_password_confirmation' => 'newPassword123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function change_password_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password', 'new_password']);
    }

    /** @test */
    public function change_password_validates_password_requirements(): void
    {
        Sanctum::actingAs($this->user);

        // Too short
        $response1 = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password123',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response1->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function change_password_validates_password_confirmation(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newPassword123',
            'new_password_confirmation' => 'differentPassword123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function change_password_revokes_all_tokens(): void
    {
        Sanctum::actingAs($this->user);

        // Create additional tokens
        $token1 = $this->user->createToken('device1')->plainTextToken;
        $token2 = $this->user->createToken('device2')->plainTextToken;

        $this->assertEquals(2, $this->user->tokens()->count()); // Two tokens created

        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newPassword123',
            'new_password_confirmation' => 'newPassword123',
        ]);

        $response->assertOk();

        // All tokens should be revoked
        $this->assertEquals(0, $this->user->tokens()->count());
    }
}
