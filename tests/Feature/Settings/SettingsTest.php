<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RequiresGdExtension;

/**
 * Settings Management Test
 *
 * Tests all settings management functionality including:
 * - Getting all settings
 * - Getting settings by group
 * - Updating organization settings
 * - Updating financial settings
 * - Updating email settings
 * - Updating security settings
 * - Updating notification settings
 * - Logo upload
 * - Cache clearing
 * - System health monitoring
 * - Authorization checks
 *
 * Requirements: REQ-636 to REQ-680
 */
class SettingsTest extends TestCase
{
    use RefreshDatabase;
    use RequiresGdExtension;

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
     * Test getting all settings as Programs Manager
     */
    public function test_programs_manager_can_get_all_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'organization',
                    'financial',
                    'email',
                    'security',
                    'notifications',
                    'general',
                ],
            ]);
    }

    /**
     * Test getting settings by group
     */
    public function test_can_get_settings_by_group(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/settings/organization');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'org_name',
                    'org_short_name',
                    'org_logo',
                ],
            ]);
    }

    /**
     * Test getting settings with invalid group returns error
     */
    public function test_getting_invalid_group_returns_error(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/settings/invalid_group');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid settings group',
            ]);
    }

    /**
     * Test updating organization settings
     */
    public function test_can_update_organization_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $data = [
            'org_name' => 'New Organization Name',
            'org_short_name' => 'NEW',
            'org_address' => '123 New Street',
            'org_phone' => '+263 123 4567',
            'org_email' => 'new@example.com',
            'org_website' => 'https://new-website.com',
        ];

        $response = $this->putJson('/api/v1/settings/organization', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organization settings updated successfully',
            ]);

        // Verify settings were updated in database
        $this->assertEquals('New Organization Name', SystemSetting::get('org_name'));
        $this->assertEquals('NEW', SystemSetting::get('org_short_name'));

        // Verify audit trail was created
        $this->assertDatabaseHas('audit_trails', [
            'user_id' => $this->programsManager->id,
            'action' => 'updated',
            'description' => 'Updated organization settings',
        ]);
    }

    /**
     * Test organization settings validation
     */
    public function test_organization_settings_validation(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->putJson('/api/v1/settings/organization', [
            'org_name' => '', // Required field
            'org_email' => 'invalid-email', // Invalid email
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['org_name', 'org_email']);
    }

    /**
     * Test updating financial settings
     */
    public function test_can_update_financial_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $data = [
            'base_currency' => 'EUR',
            'fiscal_year_start_month' => 4,
            'date_format' => 'd/m/Y',
            'datetime_format' => 'd/m/Y H:i',
        ];

        $response = $this->putJson('/api/v1/settings/financial', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Financial settings updated successfully',
            ]);

        $this->assertEquals('EUR', SystemSetting::get('base_currency'));
        $this->assertEquals(4, SystemSetting::get('fiscal_year_start_month'));
    }

    /**
     * Test financial settings validation
     */
    public function test_financial_settings_validation(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->putJson('/api/v1/settings/financial', [
            'base_currency' => 'INVALID', // Invalid currency
            'fiscal_year_start_month' => 13, // Out of range
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['base_currency', 'fiscal_year_start_month']);
    }

    /**
     * Test updating email settings
     */
    public function test_can_update_email_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $data = [
            'mail_from_address' => 'noreply@newdomain.com',
            'mail_from_name' => 'New System Name',
        ];

        $response = $this->putJson('/api/v1/settings/email', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email settings updated successfully',
            ]);

        $this->assertEquals('noreply@newdomain.com', SystemSetting::get('mail_from_address'));
    }

    /**
     * Test email settings validation
     */
    public function test_email_settings_validation(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->putJson('/api/v1/settings/email', [
            'mail_from_address' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mail_from_address']);
    }

    /**
     * Test updating security settings
     */
    public function test_can_update_security_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $data = [
            'session_timeout' => 10,
            'password_min_length' => 12,
            'max_login_attempts' => 3,
            'lockout_duration' => 30,
        ];

        $response = $this->putJson('/api/v1/settings/security', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Security settings updated successfully',
            ]);

        $this->assertEquals(10, SystemSetting::get('session_timeout'));
        $this->assertEquals(12, SystemSetting::get('password_min_length'));
    }

    /**
     * Test security settings validation
     */
    public function test_security_settings_validation(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->putJson('/api/v1/settings/security', [
            'session_timeout' => 3, // Below minimum
            'password_min_length' => 40, // Above maximum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_timeout', 'password_min_length']);
    }

    /**
     * Test updating notification settings
     */
    public function test_can_update_notification_settings(): void
    {
        Sanctum::actingAs($this->programsManager);

        $data = [
            'notifications_expense_approvals' => false,
            'notifications_budget_alerts' => true,
            'notifications_project_milestones' => false,
            'notifications_comment_mentions' => true,
            'notifications_report_generation' => false,
        ];

        $response = $this->putJson('/api/v1/settings/notifications', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification settings updated successfully',
            ]);

        $this->assertFalse((bool) SystemSetting::get('notifications_expense_approvals'));
        $this->assertTrue((bool) SystemSetting::get('notifications_budget_alerts'));
    }

    /**
     * Test uploading organization logo
     */
    public function test_can_upload_organization_logo(): void
    {
        $this->skipIfGdNotAvailable();

        Sanctum::actingAs($this->programsManager);

        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 600, 600);

        $response = $this->postJson('/api/v1/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logo uploaded successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['path'],
            ]);

        // Verify logo path was updated
        $logoPath = SystemSetting::get('org_logo');
        $this->assertStringContainsString('canzim_logo_', $logoPath);

        // Verify audit trail
        $this->assertDatabaseHas('audit_trails', [
            'user_id' => $this->programsManager->id,
            'description' => 'Updated organization logo',
        ]);
    }

    /**
     * Test logo upload validation
     */
    public function test_logo_upload_validation(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Test with non-image file
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/v1/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    /**
     * Test logo upload with oversized file
     */
    public function test_logo_upload_rejects_oversized_file(): void
    {
        $this->skipIfGdNotAvailable();

        Sanctum::actingAs($this->programsManager);

        Storage::fake('public');

        // Create file larger than 2MB
        $file = UploadedFile::fake()->image('logo.png')->size(3000);

        $response = $this->postJson('/api/v1/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    /**
     * Test clearing all caches
     */
    public function test_can_clear_all_caches(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Set some cache values
        Cache::put('test_key', 'test_value', 3600);
        SystemSetting::get('org_name'); // This should cache the setting

        $response = $this->postJson('/api/v1/settings/cache/clear');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All caches cleared successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['cleared'],
            ]);

        // Verify cache was cleared
        $this->assertNull(Cache::get('test_key'));

        // Verify audit trail
        $this->assertDatabaseHas('audit_trails', [
            'user_id' => $this->programsManager->id,
            'action' => 'cache_cleared',
            'description' => 'Cleared all system caches',
        ]);
    }

    /**
     * Test getting system health metrics
     */
    public function test_can_get_system_health(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/settings/system-health');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'disk_usage' => [
                        'total',
                        'used',
                        'free',
                        'percentage',
                    ],
                    'database_size',
                    'cache_status',
                ],
            ]);
    }

    /**
     * Test Finance Officer cannot access settings
     */
    public function test_finance_officer_cannot_access_settings(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(403);
    }

    /**
     * Test Finance Officer cannot update settings
     */
    public function test_finance_officer_cannot_update_settings(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->putJson('/api/v1/settings/organization', [
            'org_name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Finance Officer cannot upload logo
     */
    public function test_finance_officer_cannot_upload_logo(): void
    {
        $this->skipIfGdNotAvailable();

        Sanctum::actingAs($this->financeOfficer);

        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->postJson('/api/v1/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Finance Officer cannot clear cache
     */
    public function test_finance_officer_cannot_clear_cache(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->postJson('/api/v1/settings/cache/clear');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot access settings
     */
    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(401);
    }

    /**
     * Test cache is cleared after settings update
     */
    public function test_cache_is_cleared_after_settings_update(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Get setting to cache it
        $originalName = SystemSetting::get('org_name');

        // Verify it's cached
        $this->assertEquals($originalName, Cache::get('setting.org_name'));

        // Update settings
        $this->putJson('/api/v1/settings/organization', [
            'org_name' => 'Updated Name',
            'org_short_name' => 'UN',
        ]);

        // Verify cache was cleared
        $this->assertNull(Cache::get('setting.org_name'));
    }
}
