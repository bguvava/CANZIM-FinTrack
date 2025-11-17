<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use App\Models\InKindContribution;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorProjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected Project $project;

    protected Donor $donor;

    protected function setUp(): void
    {
        parent::setUp();

        $programsManagerRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $financeOfficerRole = Role::create(['name' => 'Finance Officer', 'slug' => 'finance-officer']);

        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $financeOfficerRole->id,
            'status' => 'active',
        ]);

        $this->project = Project::factory()->create(['status' => 'active']);
        $this->donor = Donor::factory()->create(['status' => 'active']);
    }

    /** @test */
    public function programs_manager_can_assign_donor_to_project()
    {
        Sanctum::actingAs($this->programsManager);

        $assignmentData = [
            'project_id' => $this->project->id,
            'funding_amount' => 50000.00,
            'is_restricted' => true,
            'funding_period_start' => '2025-01-01',
            'funding_period_end' => '2025-12-31',
            'notes' => 'Grant for climate action',
        ];

        $response = $this->postJson("/api/v1/donors/{$this->donor->id}/assign-project", $assignmentData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Donor assigned to project successfully',
            ]);

        $this->assertDatabaseHas('project_donors', [
            'donor_id' => $this->donor->id,
            'project_id' => $this->project->id,
            'funding_amount' => 50000.00,
            'is_restricted' => true,
        ]);
    }

    /** @test */
    public function finance_officer_can_assign_donor_to_project()
    {
        Sanctum::actingAs($this->financeOfficer);

        $assignmentData = [
            'project_id' => $this->project->id,
            'funding_amount' => 30000.00,
            'is_restricted' => false,
        ];

        $response = $this->postJson("/api/v1/donors/{$this->donor->id}/assign-project", $assignmentData);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function cannot_assign_donor_to_same_project_twice()
    {
        Sanctum::actingAs($this->programsManager);

        $assignmentData = [
            'project_id' => $this->project->id,
            'funding_amount' => 50000.00,
        ];

        // First assignment
        $this->postJson("/api/v1/donors/{$this->donor->id}/assign-project", $assignmentData);

        // Second assignment (should fail)
        $response = $this->postJson("/api/v1/donors/{$this->donor->id}/assign-project", $assignmentData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Donor is already assigned to this project',
            ]);
    }

    /** @test */
    public function funding_amount_must_be_positive()
    {
        Sanctum::actingAs($this->programsManager);

        $assignmentData = [
            'project_id' => $this->project->id,
            'funding_amount' => -1000.00,
        ];

        $response = $this->postJson("/api/v1/donors/{$this->donor->id}/assign-project", $assignmentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['funding_amount']);
    }

    /** @test */
    public function can_get_donor_funding_summary()
    {
        Sanctum::actingAs($this->programsManager);

        // Assign to projects
        $this->donor->projects()->attach($this->project->id, [
            'funding_amount' => 50000.00,
            'is_restricted' => true,
        ]);

        $project2 = Project::factory()->create();
        $this->donor->projects()->attach($project2->id, [
            'funding_amount' => 30000.00,
            'is_restricted' => false,
        ]);

        $response = $this->getJson("/api/v1/donors/{$this->donor->id}/funding-summary");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_funding',
                    'restricted_funding',
                    'unrestricted_funding',
                    'in_kind_total',
                    'active_projects_count',
                ],
            ]);

        $this->assertEquals(80000.00, $response->json('data.total_funding'));
        $this->assertEquals(50000.00, $response->json('data.restricted_funding'));
        $this->assertEquals(30000.00, $response->json('data.unrestricted_funding'));
    }

    /** @test */
    public function programs_manager_can_remove_donor_from_project()
    {
        Sanctum::actingAs($this->programsManager);

        $this->donor->projects()->attach($this->project->id, [
            'funding_amount' => 50000.00,
        ]);

        $response = $this->deleteJson("/api/v1/donors/{$this->donor->id}/projects/{$this->project->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Donor removed from project successfully',
            ]);

        $this->assertDatabaseMissing('project_donors', [
            'donor_id' => $this->donor->id,
            'project_id' => $this->project->id,
        ]);
    }

    /** @test */
    public function can_add_in_kind_contribution()
    {
        Sanctum::actingAs($this->programsManager);

        $inKindData = [
            'donor_id' => $this->donor->id,
            'project_id' => $this->project->id,
            'description' => 'Office equipment donation',
            'category' => 'equipment',
            'estimated_value' => 15000.00,
            'contribution_date' => '2025-01-15',
        ];

        $response = $this->postJson('/api/v1/in-kind-contributions', $inKindData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'In-kind contribution recorded successfully',
            ]);

        $this->assertDatabaseHas('in_kind_contributions', [
            'donor_id' => $this->donor->id,
            'project_id' => $this->project->id,
            'category' => 'equipment',
            'estimated_value' => 15000.00,
        ]);
    }

    /** @test */
    public function in_kind_category_must_be_valid()
    {
        Sanctum::actingAs($this->programsManager);

        $inKindData = [
            'donor_id' => $this->donor->id,
            'project_id' => $this->project->id,
            'description' => 'Test contribution',
            'category' => 'invalid_category',
            'estimated_value' => 1000.00,
            'contribution_date' => '2025-01-15',
        ];

        $response = $this->postJson('/api/v1/in-kind-contributions', $inKindData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    /** @test */
    public function can_toggle_donor_status()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/v1/donors/{$donor->id}/toggle-status");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('donors', [
            'id' => $donor->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function cannot_deactivate_donor_with_active_projects()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create(['status' => 'active']);
        $activeProject = Project::factory()->create(['status' => 'active']);

        $donor->projects()->attach($activeProject->id, ['funding_amount' => 10000.00]);

        $response = $this->postJson("/api/v1/donors/{$donor->id}/toggle-status");

        $response->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }
}
