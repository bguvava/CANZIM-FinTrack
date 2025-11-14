<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test all database seeders populate data correctly
 * REQ-074: Write Seeder Tests
 */
class SeedersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run seeders before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that roles are seeded correctly
     * REQ-063: Create Roles Seeder
     */
    public function test_roles_are_seeded_correctly(): void
    {
        $roles = DB::table('roles')->get();

        $this->assertCount(3, $roles, 'Should have exactly 3 roles');

        $roleNames = $roles->pluck('name')->toArray();
        $this->assertContains('Programs Manager', $roleNames);
        $this->assertContains('Finance Officer', $roleNames);
        $this->assertContains('Project Officer', $roleNames);

        $roleSlugs = $roles->pluck('slug')->toArray();
        $this->assertContains('programs-manager', $roleSlugs);
        $this->assertContains('finance-officer', $roleSlugs);
        $this->assertContains('project-officer', $roleSlugs);
    }

    /**
     * Test that expense categories are seeded correctly
     * REQ-064: Create Expense Categories Seeder
     */
    public function test_expense_categories_are_seeded_correctly(): void
    {
        $categories = DB::table('expense_categories')->get();

        $this->assertCount(5, $categories, 'Should have exactly 5 expense categories');

        $categoryNames = $categories->pluck('name')->toArray();
        $this->assertContains('Travel', $categoryNames);
        $this->assertContains('Staff Salaries', $categoryNames);
        $this->assertContains('Procurement/Supplies', $categoryNames);
        $this->assertContains('Consultants/Contractors', $categoryNames);
        $this->assertContains('Other', $categoryNames);

        foreach ($categories as $category) {
            $this->assertTrue(
                (bool) $category->is_active,
                "Category '{$category->name}' should be active by default"
            );
        }
    }

    /**
     * Test that admin user is seeded correctly
     * REQ-065: Create Admin User Seeder
     */
    public function test_admin_user_is_seeded_correctly(): void
    {
        $adminUser = DB::table('users')
            ->where('email', 'admin@canzim.org.zw')
            ->first();

        $this->assertNotNull($adminUser, 'Admin user should exist');
        $this->assertEquals('CANZIM Administrator', $adminUser->name);
        $this->assertEquals('admin@canzim.org.zw', $adminUser->email);
        $this->assertEquals('active', $adminUser->status);
        $this->assertEquals('Head Office', $adminUser->office_location);
        $this->assertNotNull($adminUser->password, 'Admin password should be hashed');

        $programsManagerRole = DB::table('roles')
            ->where('slug', 'programs-manager')
            ->first();

        $this->assertEquals(
            $programsManagerRole->id,
            $adminUser->role_id,
            'Admin user should have Programs Manager role'
        );
    }

    /**
     * Test that system settings are seeded correctly
     * REQ-066: Create System Settings Seeder
     */
    public function test_system_settings_are_seeded_correctly(): void
    {
        $settings = DB::table('system_settings')->get();

        $this->assertGreaterThanOrEqual(11, $settings->count(), 'Should have at least 11 system settings');

        $settingKeys = $settings->pluck('key')->toArray();

        $expectedKeys = [
            'org_name',
            'org_short_name',
            'org_logo',
            'currency',
            'timezone',
            'session_timeout',
            'date_format',
            'datetime_format',
            'max_file_size_documents',
            'max_file_size_receipts',
            'max_file_size_attachments',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertContains($key, $settingKeys, "System setting '{$key}' should exist");
        }
    }

    /**
     * Test specific system settings values
     */
    public function test_system_settings_have_correct_default_values(): void
    {
        $orgName = DB::table('system_settings')
            ->where('key', 'org_name')
            ->value('value');
        $this->assertEquals('Climate Action Network Zimbabwe', $orgName);

        $orgShortName = DB::table('system_settings')
            ->where('key', 'org_short_name')
            ->value('value');
        $this->assertEquals('CANZIM', $orgShortName);

        $currency = DB::table('system_settings')
            ->where('key', 'currency')
            ->value('value');
        $this->assertEquals('USD', $currency);

        $sessionTimeout = DB::table('system_settings')
            ->where('key', 'session_timeout')
            ->value('value');
        $this->assertEquals('5', $sessionTimeout);

        $timezone = DB::table('system_settings')
            ->where('key', 'timezone')
            ->value('value');
        $this->assertEquals('Africa/Harare', $timezone);
    }

    /**
     * Test file size limits are configured correctly
     */
    public function test_file_size_limits_are_configured_correctly(): void
    {
        $documentSize = DB::table('system_settings')
            ->where('key', 'max_file_size_documents')
            ->value('value');
        $this->assertEquals('5120', $documentSize, 'Documents should be limited to 5MB (5120KB)');

        $receiptSize = DB::table('system_settings')
            ->where('key', 'max_file_size_receipts')
            ->value('value');
        $this->assertEquals('5120', $receiptSize, 'Receipts should be limited to 5MB (5120KB)');

        $attachmentSize = DB::table('system_settings')
            ->where('key', 'max_file_size_attachments')
            ->value('value');
        $this->assertEquals('2048', $attachmentSize, 'Attachments should be limited to 2MB (2048KB)');
    }

    /**
     * Test that roles have unique slugs
     */
    public function test_roles_have_unique_slugs(): void
    {
        $roleSlugs = DB::table('roles')->pluck('slug')->toArray();
        $uniqueSlugs = array_unique($roleSlugs);

        $this->assertEquals(
            count($roleSlugs),
            count($uniqueSlugs),
            'All role slugs should be unique'
        );
    }

    /**
     * Test that expense categories have unique slugs
     */
    public function test_expense_categories_have_unique_slugs(): void
    {
        $categorySlugs = DB::table('expense_categories')->pluck('slug')->toArray();
        $uniqueSlugs = array_unique($categorySlugs);

        $this->assertEquals(
            count($categorySlugs),
            count($uniqueSlugs),
            'All expense category slugs should be unique'
        );
    }

    /**
     * Test that all seeded data has timestamps
     */
    public function test_seeded_data_has_timestamps(): void
    {
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $this->assertNotNull($role->created_at, "Role '{$role->name}' should have created_at timestamp");
            $this->assertNotNull($role->updated_at, "Role '{$role->name}' should have updated_at timestamp");
        }

        $categories = DB::table('expense_categories')->get();
        foreach ($categories as $category) {
            $this->assertNotNull($category->created_at, "Category '{$category->name}' should have created_at timestamp");
            $this->assertNotNull($category->updated_at, "Category '{$category->name}' should have updated_at timestamp");
        }

        $settings = DB::table('system_settings')->get();
        foreach ($settings as $setting) {
            $this->assertNotNull($setting->created_at, "Setting '{$setting->key}' should have created_at timestamp");
            $this->assertNotNull($setting->updated_at, "Setting '{$setting->key}' should have updated_at timestamp");
        }
    }

    /**
     * Test Programs Manager role has correct description
     */
    public function test_programs_manager_role_has_correct_description(): void
    {
        $role = DB::table('roles')
            ->where('slug', 'programs-manager')
            ->first();

        $this->assertStringContainsString(
            'Highest authority',
            $role->description,
            'Programs Manager should be described as highest authority'
        );
    }

    /**
     * Test Finance Officer role has correct description
     */
    public function test_finance_officer_role_has_correct_description(): void
    {
        $role = DB::table('roles')
            ->where('slug', 'finance-officer')
            ->first();

        $this->assertStringContainsString(
            'Middle authority',
            $role->description,
            'Finance Officer should be described as middle authority'
        );
    }

    /**
     * Test Project Officer role has correct description
     */
    public function test_project_officer_role_has_correct_description(): void
    {
        $role = DB::table('roles')
            ->where('slug', 'project-officer')
            ->first();

        $this->assertStringContainsString(
            'Base authority',
            $role->description,
            'Project Officer should be described as base authority'
        );
    }

    /**
     * Test that database seeder can be run multiple times without errors
     * REQ-068: Run All Seeders
     */
    public function test_seeders_can_run_multiple_times_without_duplicate_errors(): void
    {
        try {
            $this->seed();
            $this->assertTrue(true, 'Seeders should handle multiple runs gracefully');
        } catch (\Exception $e) {
            $this->fail('Seeders should not throw exceptions when run multiple times: '.$e->getMessage());
        }
    }
}
