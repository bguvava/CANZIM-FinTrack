<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Activity Log Controller Tests
 */
class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $programsManager;

    private User $financeOfficer;

    private User $projectOfficer;

    private Role $programsManagerRole;

    private Role $financeOfficerRole;

    private Role $projectOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Manages all programs',
        ]);

        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
            'description' => 'Manages finances',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
            'description' => 'Manages projects',
        ]);

        // Create test users
        $this->programsManager = User::factory()->create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->programsManagerRole->id,
            'office_location' => 'Harare Office',
            'status' => 'active',
        ]);

        $this->financeOfficer = User::factory()->create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->financeOfficerRole->id,
            'office_location' => 'Harare Office',
            'status' => 'active',
        ]);

        $this->projectOfficer = User::factory()->create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->projectOfficerRole->id,
            'office_location' => 'Bulawayo Office',
            'status' => 'active',
        ]);

        // Create sample activity logs
        ActivityLog::create([
            'user_id' => $this->programsManager->id,
            'activity_type' => 'user_created',
            'description' => 'Created new user: Test User',
            'properties' => json_encode(['user_name' => 'Test User']),
            'created_at' => now()->subDays(5),
        ]);

        ActivityLog::create([
            'user_id' => $this->financeOfficer->id,
            'activity_type' => 'profile_updated',
            'description' => 'Updated profile information',
            'properties' => null,
            'created_at' => now()->subDays(3),
        ]);

        ActivityLog::create([
            'user_id' => $this->projectOfficer->id,
            'activity_type' => 'password_changed',
            'description' => 'Changed password',
            'properties' => null,
            'created_at' => now()->subDays(1),
        ]);
    }

    /** @test */
    public function programs_manager_can_list_all_activity_logs(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'activity_type',
                        'description',
                        'properties',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Should have 3 activity logs
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function non_programs_manager_cannot_list_activity_logs(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_list_activity_logs(): void
    {
        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertUnauthorized();
    }

    /** @test */
    public function can_filter_activity_logs_by_user_id(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson("/api/v1/activity-logs?user_id={$this->financeOfficer->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->financeOfficer->id, $response->json('data.0.user.id'));
    }

    /** @test */
    public function can_filter_activity_logs_by_activity_type(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/activity-logs?activity_type=password_changed');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('password_changed', $response->json('data.0.activity_type'));
    }

    /** @test */
    public function can_filter_activity_logs_by_date_range(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Use very wide date range to ensure we get the test logs
        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $dateTo = now()->addDay()->format('Y-m-d');

        $response = $this->getJson("/api/v1/activity-logs?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertOk();
        // Should get all 3 logs created in setup
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    /** @test */
    public function programs_manager_can_view_any_user_activity(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson("/api/v1/users/{$this->financeOfficer->id}/activity");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'activity_type',
                        'description',
                        'created_at',
                    ],
                ],
                'meta',
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->financeOfficer->id, $response->json('data.0.user.id'));
    }

    /** @test */
    public function user_can_view_own_activity(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson("/api/v1/users/{$this->financeOfficer->id}/activity");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->financeOfficer->id, $response->json('data.0.user.id'));
    }

    /** @test */
    public function user_cannot_view_other_user_activity(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson("/api/v1/users/{$this->projectOfficer->id}/activity");

        $response->assertForbidden();
    }

    /** @test */
    public function programs_manager_can_bulk_delete_logs(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create old logs that will definitely be in range
        // Use dates much older than the setUp logs (which are 5, 3, 1 days ago)
        ActivityLog::create([
            'user_id' => $this->programsManager->id,
            'activity_type' => 'old_activity',
            'description' => 'Old activity 1',
            'properties' => null,
            'created_at' => now()->subDays(20)->startOfDay(),
        ]);

        ActivityLog::create([
            'user_id' => $this->programsManager->id,
            'activity_type' => 'old_activity',
            'description' => 'Old activity 2',
            'properties' => null,
            'created_at' => now()->subDays(15)->startOfDay(),
        ]);

        // Now we have 5 logs total (3 from setUp + 2 just created)
        $this->assertEquals(5, ActivityLog::count());

        // Delete logs from 30 days ago to 10 days ago (should delete only the 2 old_activity logs)
        $dateFrom = now()->subDays(30)->format('Y-m-d');
        $dateTo = now()->subDays(10)->format('Y-m-d');

        $response = $this->postJson('/api/v1/activity-logs/bulk-delete', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $response->assertOk();

        // Verify the count in response
        $deletedCount = $response->json('data.count');
        $this->assertEquals(2, $deletedCount);

        // Verify logs were deleted - 3 from setUp + 1 bulk delete log = 4 remain
        $this->assertEquals(4, ActivityLog::count());
    }

    /** @test */
    public function non_programs_manager_cannot_bulk_delete_logs(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->postJson('/api/v1/activity-logs/bulk-delete', [
            'date_from' => now()->subDays(6)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function bulk_delete_validates_required_fields(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/activity-logs/bulk-delete', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date_from', 'date_to']);
    }

    /** @test */
    public function bulk_delete_validates_date_range(): void
    {
        Sanctum::actingAs($this->programsManager);

        // date_to before date_from should fail
        $response = $this->postJson('/api/v1/activity-logs/bulk-delete', [
            'date_from' => now()->format('Y-m-d'),
            'date_to' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date_to']);
    }

    /** @test */
    public function bulk_delete_logs_activity(): void
    {
        Sanctum::actingAs($this->programsManager);

        $initialCount = ActivityLog::count();

        $dateFrom = now()->subDays(6)->format('Y-m-d');
        $dateTo = now()->subDays(2)->format('Y-m-d');

        $this->postJson('/api/v1/activity-logs/bulk-delete', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        // Should have logged the bulk delete activity
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->programsManager->id,
            'activity_type' => 'logs_bulk_deleted',
        ]);
    }

    /** @test */
    public function activity_logs_are_paginated(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create more logs for pagination test
        for ($i = 0; $i < 30; $i++) {
            ActivityLog::create([
                'user_id' => $this->programsManager->id,
                'activity_type' => 'test_activity',
                'description' => "Test activity {$i}",
                'properties' => null,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->getJson('/api/v1/activity-logs?per_page=10');

        $response->assertOk();
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(33, $response->json('meta.total')); // 30 + 3 from setUp
    }

    /** @test */
    public function user_activity_is_paginated(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create more logs for pagination test
        for ($i = 0; $i < 15; $i++) {
            ActivityLog::create([
                'user_id' => $this->programsManager->id,
                'activity_type' => 'test_activity',
                'description' => "Test activity {$i}",
                'properties' => null,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->getJson("/api/v1/users/{$this->programsManager->id}/activity?per_page=5");

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(5, $response->json('meta.per_page'));
        $this->assertEquals(16, $response->json('meta.total')); // 15 + 1 from setUp
    }
}
