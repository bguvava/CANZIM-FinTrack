<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RequiresGdExtension;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    use RequiresGdExtension;

    protected UserService $userService;

    protected Role $role;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService;

        // Create a test role
        $this->role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role for unit tests',
        ]);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role_id' => $this->role->id,
            'office_location' => 'Harare',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_get_paginated_users_list(): void
    {
        // Create additional users
        User::factory()->count(5)->create(['role_id' => $this->role->id]);

        $result = $this->userService->getUsersList();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(6, $result->total()); // 5 + 1 from setUp
        $this->assertCount(6, $result->items());
    }

    /** @test */
    public function it_can_filter_users_by_search(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'role_id' => $this->role->id,
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'role_id' => $this->role->id,
        ]);

        $result = $this->userService->getUsersList(['search' => 'John']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('John Doe', $result->items()[0]->name);
    }

    /** @test */
    public function it_can_filter_users_by_role(): void
    {
        $otherRole = Role::create([
            'name' => 'Other Role',
            'slug' => 'other-role',
            'description' => 'Another role',
        ]);

        User::factory()->count(3)->create(['role_id' => $otherRole->id]);

        $result = $this->userService->getUsersList(['role_id' => $otherRole->id]);

        $this->assertEquals(3, $result->total());
        foreach ($result->items() as $user) {
            $this->assertEquals($otherRole->id, $user->role_id);
        }
    }

    /** @test */
    public function it_can_filter_users_by_status(): void
    {
        User::factory()->create(['role_id' => $this->role->id, 'status' => 'active']);
        User::factory()->create(['role_id' => $this->role->id, 'status' => 'inactive']);

        $result = $this->userService->getUsersList(['status' => 'inactive']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('inactive', $result->items()[0]->status);
    }

    /** @test */
    public function it_can_filter_users_by_office_location(): void
    {
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => 'Bulawayo']);
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => 'Mutare']);

        $result = $this->userService->getUsersList(['office_location' => 'Bulawayo']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Bulawayo', $result->items()[0]->office_location);
    }

    /** @test */
    public function it_can_create_a_new_user(): void
    {
        $data = [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'phone_number' => '+263771234567',
            'office_location' => 'Harare',
            'role_id' => $this->role->id,
            'password' => 'Password123!',
        ];

        $user = $this->userService->createUser($data, $this->user->id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('New User', $user->name);
        $this->assertEquals('newuser@test.com', $user->email);
        $this->assertEquals('+263771234567', $user->phone_number);
        $this->assertEquals('Harare', $user->office_location);
        $this->assertEquals($this->role->id, $user->role_id);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        // Verify email was verified
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'newuser@test.com',
        ]);
        $this->assertNotNull(User::find($user->id)->email_verified_at);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'user_created',
        ]);
    }

    /** @test */
    public function it_can_update_a_user(): void
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'phone_number' => '+263771111111',
            'office_location' => 'Bulawayo',
        ];

        $updatedUser = $this->userService->updateUser($this->user, $data, $this->user->id);

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('updated@test.com', $updatedUser->email);
        $this->assertEquals('+263771111111', $updatedUser->phone_number);
        $this->assertEquals('Bulawayo', $updatedUser->office_location);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'user_updated',
        ]);
    }

    /** @test */
    public function it_does_not_log_activity_when_no_changes_are_made(): void
    {
        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];

        $initialLogCount = ActivityLog::count();

        $this->userService->updateUser($this->user, $data, $this->user->id);

        // No activity log should be created
        $this->assertEquals($initialLogCount, ActivityLog::count());
    }

    /** @test */
    public function it_can_deactivate_a_user(): void
    {
        // Create a token for the user
        Sanctum::actingAs($this->user);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $this->assertEquals(1, $this->user->tokens()->count());

        $deactivatedUser = $this->userService->deactivateUser($this->user, $this->user->id);

        $this->assertEquals('inactive', $deactivatedUser->status);

        // Verify all tokens were revoked
        $this->assertEquals(0, $this->user->tokens()->count());

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'user_deactivated',
        ]);
    }

    /** @test */
    public function it_can_activate_a_user(): void
    {
        $this->user->update(['status' => 'inactive']);

        $activatedUser = $this->userService->activateUser($this->user, $this->user->id);

        $this->assertEquals('active', $activatedUser->status);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'user_activated',
        ]);
    }

    /** @test */
    public function it_can_delete_a_user(): void
    {
        // Create an admin user who will perform the deletion
        $adminUser = User::factory()->create(['role_id' => $this->role->id]);

        // Create a token for the user to be deleted
        Sanctum::actingAs($this->user);
        $this->user->createToken('test-token');

        $this->assertEquals(1, $this->user->tokens()->count());

        // Admin deletes another user (not themselves)
        $result = $this->userService->deleteUser($this->user, $adminUser->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $this->user->id]);

        // Verify all tokens were revoked
        $this->assertEquals(0, $this->user->tokens()->count());

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $adminUser->id,
            'activity_type' => 'user_deleted',
        ]);
    }

    /** @test */
    public function it_prevents_self_deletion(): void
    {
        // Create a token for the user
        Sanctum::actingAs($this->user);
        $this->user->createToken('test-token');

        // Attempt self-deletion should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete your own account.');

        $this->userService->deleteUser($this->user, $this->user->id);
    }

    /** @test */
    public function it_can_update_user_profile(): void
    {
        $data = [
            'name' => 'Profile Updated',
            'email' => 'profileupdated@test.com',
            'phone_number' => '+263772222222',
        ];

        $updatedUser = $this->userService->updateProfile($this->user, $data);

        $this->assertEquals('Profile Updated', $updatedUser->name);
        $this->assertEquals('profileupdated@test.com', $updatedUser->email);
        $this->assertEquals('+263772222222', $updatedUser->phone_number);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'profile_updated',
        ]);
    }

    /** @test */
    public function it_can_change_user_password(): void
    {
        // Create multiple tokens
        Sanctum::actingAs($this->user);
        $currentToken = $this->user->createToken('current-token');
        $this->user->createToken('old-token-1');
        $this->user->createToken('old-token-2');

        $this->assertEquals(3, $this->user->tokens()->count());

        // Set the current token
        $this->user->withAccessToken($currentToken->accessToken);

        $result = $this->userService->changePassword($this->user, 'NewPassword123!');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('NewPassword123!', $this->user->fresh()->password));

        // Verify old tokens were revoked but current token remains
        $this->assertEquals(1, $this->user->tokens()->count());

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'password_changed',
        ]);
    }

    /** @test */
    public function it_can_upload_and_resize_avatar(): void
    {
        $this->skipIfGdNotAvailable();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $updatedUser = $this->userService->uploadAvatar($this->user, $file);

        $this->assertNotNull($updatedUser->avatar_path);
        $this->assertStringContainsString('avatars/avatar_', $updatedUser->avatar_path);

        // Verify file was stored
        Storage::disk('public')->assertExists($updatedUser->avatar_path);

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'avatar_uploaded',
        ]);
    }

    /** @test */
    public function it_deletes_old_avatar_when_uploading_new_one(): void
    {
        $this->skipIfGdNotAvailable();

        Storage::fake('public');

        // Upload first avatar
        $file1 = UploadedFile::fake()->image('avatar1.jpg');
        $this->userService->uploadAvatar($this->user, $file1);

        // Get the path from database
        $user = User::find($this->user->id);
        $firstPath = $user->avatar_path;

        $this->assertNotNull($firstPath);

        // Upload second avatar with refreshed user - wait 1 second to ensure different filename
        sleep(1);
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        $updatedUser = $this->userService->uploadAvatar($user, $file2);
        $secondPath = $updatedUser->avatar_path;

        // Paths should be different (different timestamps)
        $this->assertNotEquals($firstPath, $secondPath);

        // New avatar should exist in storage
        $this->assertTrue(Storage::disk('public')->exists($secondPath));

        // Old avatar should be deleted from storage
        $this->assertFalse(Storage::disk('public')->exists($firstPath));
    }

    /** @test */
    public function it_can_get_user_activity(): void
    {
        // Create activity logs for the user
        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'login',
            'description' => 'User logged in',
            'created_at' => now()->subDays(1),
        ]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'logout',
            'description' => 'User logged out',
            'created_at' => now(),
        ]);

        $result = $this->userService->getUserActivity($this->user->id);

        $this->assertEquals(2, $result->total());
        $this->assertEquals('logout', $result->items()[0]->activity_type); // Most recent first
    }

    /** @test */
    public function it_can_get_activity_logs_with_filters(): void
    {
        $otherUser = User::factory()->create(['role_id' => $this->role->id]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'login',
            'description' => 'User logged in',
            'created_at' => now()->subDays(5),
        ]);

        ActivityLog::create([
            'user_id' => $otherUser->id,
            'activity_type' => 'logout',
            'description' => 'User logged out',
            'created_at' => now()->subDays(3),
        ]);

        // Filter by user_id
        $result = $this->userService->getActivityLogs(['user_id' => $this->user->id]);
        $this->assertEquals(1, $result->total());

        // Filter by activity_type
        $result = $this->userService->getActivityLogs(['activity_type' => 'login']);
        $this->assertEquals(1, $result->total());

        // Filter by date range
        $result = $this->userService->getActivityLogs([
            'date_from' => now()->subDays(6)->format('Y-m-d'),
            'date_to' => now()->subDays(4)->format('Y-m-d'),
        ]);
        $this->assertEquals(1, $result->total());
    }

    /** @test */
    public function it_can_bulk_delete_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'old_log',
            'description' => 'Old log 1',
            'created_at' => now()->subDays(30)->startOfDay(),
        ]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'old_log',
            'description' => 'Old log 2',
            'created_at' => now()->subDays(25)->startOfDay(),
        ]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'activity_type' => 'recent_log',
            'description' => 'Recent log',
            'created_at' => now()->subDays(5),
        ]);

        $this->assertEquals(3, ActivityLog::count());

        // Delete logs from 35 days ago to 20 days ago
        $count = $this->userService->bulkDeleteLogs(
            now()->subDays(35)->format('Y-m-d'),
            now()->subDays(20)->format('Y-m-d'),
            $this->user->id
        );

        $this->assertEquals(2, $count);

        // 1 recent log + 1 bulk delete log = 2 remain
        $this->assertEquals(2, ActivityLog::count());

        // Verify bulk delete log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'activity_type' => 'logs_bulk_deleted',
        ]);
    }

    /** @test */
    public function it_can_get_office_locations(): void
    {
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => 'Harare']);
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => 'Bulawayo']);
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => 'Harare']); // Duplicate
        User::factory()->create(['role_id' => $this->role->id, 'office_location' => null]); // Null

        $locations = $this->userService->getOfficeLocations();

        $this->assertCount(2, $locations); // Harare, Bulawayo (from setUp + new ones)
        $this->assertTrue($locations->contains('Harare'));
        $this->assertTrue($locations->contains('Bulawayo'));
    }
}
