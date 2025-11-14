<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected Role $programsManagerRole;

    protected Role $financeOfficerRole;

    protected Role $projectOfficerRole;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;

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

        // Create users
        $this->programsManager = User::factory()->create([
            'role_id' => $this->programsManagerRole->id,
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
        ]);

        $this->financeOfficer = User::factory()->create([
            'role_id' => $this->financeOfficerRole->id,
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
        ]);

        $this->projectOfficer = User::factory()->create([
            'role_id' => $this->projectOfficerRole->id,
            'name' => 'Project Officer',
            'email' => 'po@test.com',
        ]);
    }

    /** @test */
    public function programs_manager_can_view_any_users(): void
    {
        $this->assertTrue($this->policy->viewAny($this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_view_any_users(): void
    {
        $this->assertFalse($this->policy->viewAny($this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_view_any_users(): void
    {
        $this->assertFalse($this->policy->viewAny($this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_view_any_user(): void
    {
        $this->assertTrue($this->policy->view($this->programsManager, $this->financeOfficer));
        $this->assertTrue($this->policy->view($this->programsManager, $this->projectOfficer));
    }

    /** @test */
    public function user_can_view_themselves(): void
    {
        $this->assertTrue($this->policy->view($this->financeOfficer, $this->financeOfficer));
        $this->assertTrue($this->policy->view($this->projectOfficer, $this->projectOfficer));
    }

    /** @test */
    public function user_cannot_view_other_users(): void
    {
        $this->assertFalse($this->policy->view($this->financeOfficer, $this->projectOfficer));
        $this->assertFalse($this->policy->view($this->projectOfficer, $this->financeOfficer));
    }

    /** @test */
    public function programs_manager_can_create_users(): void
    {
        $this->assertTrue($this->policy->create($this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_create_users(): void
    {
        $this->assertFalse($this->policy->create($this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_create_users(): void
    {
        $this->assertFalse($this->policy->create($this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_update_any_user(): void
    {
        $this->assertTrue($this->policy->update($this->programsManager, $this->financeOfficer));
        $this->assertTrue($this->policy->update($this->programsManager, $this->projectOfficer));
    }

    /** @test */
    public function user_can_update_themselves(): void
    {
        $this->assertTrue($this->policy->update($this->financeOfficer, $this->financeOfficer));
        $this->assertTrue($this->policy->update($this->projectOfficer, $this->projectOfficer));
    }

    /** @test */
    public function user_cannot_update_other_users(): void
    {
        $this->assertFalse($this->policy->update($this->financeOfficer, $this->projectOfficer));
        $this->assertFalse($this->policy->update($this->projectOfficer, $this->financeOfficer));
    }

    /** @test */
    public function programs_manager_can_delete_other_users(): void
    {
        $this->assertTrue($this->policy->delete($this->programsManager, $this->financeOfficer));
        $this->assertTrue($this->policy->delete($this->programsManager, $this->projectOfficer));
    }

    /** @test */
    public function programs_manager_cannot_delete_themselves(): void
    {
        $this->assertFalse($this->policy->delete($this->programsManager, $this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_delete_users(): void
    {
        $this->assertFalse($this->policy->delete($this->financeOfficer, $this->projectOfficer));
        $this->assertFalse($this->policy->delete($this->financeOfficer, $this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_delete_users(): void
    {
        $this->assertFalse($this->policy->delete($this->projectOfficer, $this->financeOfficer));
        $this->assertFalse($this->policy->delete($this->projectOfficer, $this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_deactivate_other_users(): void
    {
        $this->assertTrue($this->policy->deactivate($this->programsManager, $this->financeOfficer));
        $this->assertTrue($this->policy->deactivate($this->programsManager, $this->projectOfficer));
    }

    /** @test */
    public function programs_manager_cannot_deactivate_themselves(): void
    {
        $this->assertFalse($this->policy->deactivate($this->programsManager, $this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_deactivate_users(): void
    {
        $this->assertFalse($this->policy->deactivate($this->financeOfficer, $this->projectOfficer));
        $this->assertFalse($this->policy->deactivate($this->financeOfficer, $this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_deactivate_users(): void
    {
        $this->assertFalse($this->policy->deactivate($this->projectOfficer, $this->financeOfficer));
        $this->assertFalse($this->policy->deactivate($this->projectOfficer, $this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_activate_users(): void
    {
        $this->assertTrue($this->policy->activate($this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_activate_users(): void
    {
        $this->assertFalse($this->policy->activate($this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_activate_users(): void
    {
        $this->assertFalse($this->policy->activate($this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_view_activity_logs(): void
    {
        $this->assertTrue($this->policy->viewActivityLogs($this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_view_activity_logs(): void
    {
        $this->assertFalse($this->policy->viewActivityLogs($this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_view_activity_logs(): void
    {
        $this->assertFalse($this->policy->viewActivityLogs($this->projectOfficer));
    }

    /** @test */
    public function programs_manager_can_bulk_delete_logs(): void
    {
        $this->assertTrue($this->policy->bulkDeleteLogs($this->programsManager));
    }

    /** @test */
    public function finance_officer_cannot_bulk_delete_logs(): void
    {
        $this->assertFalse($this->policy->bulkDeleteLogs($this->financeOfficer));
    }

    /** @test */
    public function project_officer_cannot_bulk_delete_logs(): void
    {
        $this->assertFalse($this->policy->bulkDeleteLogs($this->projectOfficer));
    }
}
