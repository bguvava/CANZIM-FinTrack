<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\AuditTrail;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Audit Trail Test
 *
 * Tests all audit trail functionality including:
 * - Listing audit trails with pagination
 * - Filtering by user, action, auditable type
 * - Filtering by date range
 * - Searching audit trails
 * - Viewing single audit trail
 * - Exporting to CSV
 * - Getting filter options
 * - Automatic audit trail creation on settings update
 * - Authorization checks
 *
 * Requirements: REQ-651 to REQ-670
 */
class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected Role $programsManagerRole;

    protected Role $financeOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        // Create test users
        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);

        // Seed default settings
        $this->seed(\Database\Seeders\SystemSettingsSeeder::class);
    }

    /**
     * Test Programs Manager can list audit trails
     */
    public function test_programs_manager_can_list_audit_trails(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create some audit trails
        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => ['org_name' => 'Old Name'],
            'new_values' => ['org_name' => 'New Name'],
            'description' => 'Updated organization settings',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'request_url' => '/api/v1/settings/organization',
            'request_method' => 'PUT',
        ]);

        $response = $this->getJson('/api/v1/audit-trails');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'action',
                            'auditable_type',
                            'description',
                            'ip_address',
                            'created_at',
                            'user' => [
                                'id',
                                'name',
                                'email',
                            ],
                        ],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Test filtering audit trails by user
     */
    public function test_can_filter_audit_trails_by_user(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create audit trails for different users
        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Action by Programs Manager',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->financeOfficer->id,
            'action' => 'created',
            'auditable_type' => 'App\Models\Expense',
            'description' => 'Action by Finance Officer',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails?user_id='.$this->programsManager->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->programsManager->id, $data[0]['user_id']);
    }

    /**
     * Test filtering audit trails by action
     */
    public function test_can_filter_audit_trails_by_action(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated settings',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'cache_cleared',
            'auditable_type' => 'System',
            'description' => 'Cleared cache',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails?action=updated');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        foreach ($data as $trail) {
            $this->assertEquals('updated', $trail['action']);
        }
    }

    /**
     * Test filtering audit trails by auditable type
     */
    public function test_can_filter_audit_trails_by_auditable_type(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated settings',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'created',
            'auditable_type' => 'App\Models\Project',
            'description' => 'Created project',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails?auditable_type=SystemSetting');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        foreach ($data as $trail) {
            $this->assertStringContainsString('SystemSetting', $trail['auditable_type']);
        }
    }

    /**
     * Test filtering audit trails by date range
     */
    public function test_can_filter_audit_trails_by_date_range(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create audit trail in the past
        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Old action',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(10),
        ]);

        // Create recent audit trail
        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Recent action',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $startDate = now()->subDays(5)->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/v1/audit-trails?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Recent action', $data[0]['description']);
    }

    /**
     * Test searching audit trails
     */
    public function test_can_search_audit_trails(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated organization settings',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'cache_cleared',
            'auditable_type' => 'System',
            'description' => 'Cleared all caches',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails?search=organization');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertGreaterThan(0, count($data));
        $this->assertStringContainsString('organization', strtolower($data[0]['description']));
    }

    /**
     * Test viewing single audit trail
     */
    public function test_can_view_single_audit_trail(): void
    {
        Sanctum::actingAs($this->programsManager);

        $auditTrail = AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => ['org_name' => 'Old Name'],
            'new_values' => ['org_name' => 'New Name'],
            'description' => 'Updated organization settings',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'request_url' => '/api/v1/settings/organization',
            'request_method' => 'PUT',
        ]);

        $response = $this->getJson("/api/v1/audit-trails/{$auditTrail->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $auditTrail->id,
                    'action' => 'updated',
                    'description' => 'Updated organization settings',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'action',
                    'auditable_type',
                    'old_values',
                    'new_values',
                    'description',
                    'ip_address',
                    'user_agent',
                    'request_url',
                    'request_method',
                    'created_at',
                    'user',
                ],
            ]);
    }

    /**
     * Test exporting audit trails to CSV
     */
    public function test_can_export_audit_trails_to_csv(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated settings',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails/export');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'filename',
                    'content',
                ],
            ]);

        // Decode and verify CSV content
        $csvContent = base64_decode($response->json('data.content'));
        $this->assertStringContainsString('ID,User,Action,Module,Description,IP Address,Date/Time', $csvContent);
        $this->assertStringContainsString('Updated settings', $csvContent);
    }

    /**
     * Test exporting with filters applied
     */
    public function test_export_respects_filters(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated organization settings',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'cache_cleared',
            'auditable_type' => 'System',
            'description' => 'Cleared cache',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails/export?action=updated');

        $response->assertStatus(200);

        $csvContent = base64_decode($response->json('data.content'));
        $this->assertStringContainsString('Updated organization settings', $csvContent);
        $this->assertStringNotContainsString('Cleared cache', $csvContent);
    }

    /**
     * Test getting filter options
     */
    public function test_can_get_filter_options(): void
    {
        Sanctum::actingAs($this->programsManager);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated settings',
            'ip_address' => '127.0.0.1',
        ]);

        AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'cache_cleared',
            'auditable_type' => 'System',
            'description' => 'Cleared cache',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/audit-trails/filters');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'actions',
                    'auditable_types',
                ],
            ]);

        $actions = $response->json('data.actions');
        $this->assertContains('updated', $actions);
        $this->assertContains('cache_cleared', $actions);
    }

    /**
     * Test audit trail is created when settings are updated
     */
    public function test_audit_trail_created_on_settings_update(): void
    {
        Sanctum::actingAs($this->programsManager);

        $this->assertDatabaseCount('audit_trails', 0);

        $this->putJson('/api/v1/settings/organization', [
            'org_name' => 'Updated Name',
            'org_short_name' => 'UN',
        ]);

        $this->assertDatabaseCount('audit_trails', 1);

        $this->assertDatabaseHas('audit_trails', [
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated organization settings',
        ]);

        $auditTrail = AuditTrail::first();
        $this->assertNotNull($auditTrail->old_values);
        $this->assertNotNull($auditTrail->new_values);
        $this->assertNotNull($auditTrail->ip_address);
        $this->assertNotNull($auditTrail->user_agent);
        $this->assertNotNull($auditTrail->request_url);
        $this->assertEquals('PUT', $auditTrail->request_method);
    }

    /**
     * Test audit trail captures old and new values correctly
     */
    public function test_audit_trail_captures_old_and_new_values(): void
    {
        Sanctum::actingAs($this->programsManager);

        $this->putJson('/api/v1/settings/organization', [
            'org_name' => 'New Organization Name',
            'org_short_name' => 'NEW',
        ]);

        $auditTrail = AuditTrail::first();

        $oldValues = $auditTrail->old_values;
        $newValues = $auditTrail->new_values;

        $this->assertEquals('Climate Action Network Zimbabwe', $oldValues['org_name']);
        $this->assertEquals('New Organization Name', $newValues['org_name']);
    }

    /**
     * Test Finance Officer cannot access audit trails
     */
    public function test_finance_officer_cannot_access_audit_trails(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/audit-trails');

        $response->assertStatus(403);
    }

    /**
     * Test Finance Officer cannot view single audit trail
     */
    public function test_finance_officer_cannot_view_audit_trail(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $auditTrail = AuditTrail::create([
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'description' => 'Updated settings',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->getJson("/api/v1/audit-trails/{$auditTrail->id}");

        $response->assertStatus(403);
    }

    /**
     * Test Finance Officer cannot export audit trails
     */
    public function test_finance_officer_cannot_export_audit_trails(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/audit-trails/export');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot access audit trails
     */
    public function test_unauthenticated_user_cannot_access_audit_trails(): void
    {
        $response = $this->getJson('/api/v1/audit-trails');

        $response->assertStatus(401);
    }

    /**
     * Test pagination works correctly
     */
    public function test_audit_trails_pagination_works(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Create 60 audit trails
        for ($i = 0; $i < 60; $i++) {
            AuditTrail::create([
                'user_id' => $this->programsManager->id,
                'action' => 'updated',
                'auditable_type' => SystemSetting::class,
                'description' => "Test action {$i}",
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->getJson('/api/v1/audit-trails');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(50, $data['data']); // Default pagination is 50 per page
        $this->assertEquals(60, $data['total']);
        $this->assertEquals(1, $data['current_page']);
    }
}
