<?php

namespace Tests\Feature\Projects;

use App\Models\Budget;
use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Donor $donor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $pmRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Full access',
        ]);
        $foRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
            'description' => 'Financial access',
        ]);
        $poRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
            'description' => 'Project access',
        ]);

        // Create users
        $this->programsManager = User::factory()->create(['role_id' => $pmRole->id]);
        $this->financeOfficer = User::factory()->create(['role_id' => $foRole->id]);
        $this->projectOfficer = User::factory()->create(['role_id' => $poRole->id]);

        // Create donor
        $this->donor = Donor::factory()->create();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(401);
    }

    /** @test */
    public function programs_manager_can_view_projects_list(): void
    {
        Project::factory()->count(5)->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'code', 'name', 'status', 'total_budget'],
                    ],
                ],
            ]);
    }

    /** @test */
    public function programs_manager_can_create_project(): void
    {
        $projectData = [
            'name' => 'Test Climate Project',
            'description' => 'Testing project creation',
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
            'total_budget' => 500000,
            'status' => 'planning',
            'office_location' => 'Harare',
            'donors' => [
                [
                    'donor_id' => $this->donor->id,
                    'funding_amount' => 500000,
                    'is_restricted' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Test Climate Project');

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Climate Project',
            'created_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function project_code_is_auto_generated(): void
    {
        $projectData = [
            'name' => 'Auto Code Project',
            'description' => 'Testing auto code generation',
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(201);
        $project = Project::first();
        $this->assertMatchesRegularExpression('/^PROJ-\d{4}-\d{4}$/', $project->code);
    }

    /** @test */
    public function finance_officer_cannot_create_project(): void
    {
        $projectData = [
            'name' => 'Test Project',
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
        ];

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(403);
    }

    /** @test */
    public function programs_manager_can_update_project(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->programsManager->id,
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function programs_manager_can_assign_team_members(): void
    {
        $project = Project::factory()->create();

        $projectData = [
            'team_members' => [
                ['user_id' => $this->projectOfficer->id, 'role' => 'team_member'],
            ],
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", $projectData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $this->projectOfficer->id,
        ]);
    }

    /** @test */
    public function programs_manager_can_archive_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/archive");

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'cancelled', // archived projects use 'cancelled' status
        ]);
    }

    /** @test */
    public function can_create_budget_for_project(): void
    {
        $project = Project::factory()->create();

        // Assign a donor with sufficient funding
        $donor = Donor::factory()->create();
        $project->donors()->attach($donor->id, [
            'funding_amount' => 200000, // Sufficient for our budget
            'is_restricted' => false,
        ]);

        $budgetData = [
            'project_id' => $project->id,
            'fiscal_year' => '2025',
            'items' => [
                [
                    'category' => 'Travel',
                    'description' => 'Project travel expenses',
                    'allocated_amount' => 50000,
                ],
                [
                    'category' => 'Staff Salaries',
                    'description' => 'Staff costs',
                    'allocated_amount' => 100000,
                ],
            ],
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('budgets', [
            'project_id' => $project->id,
            'fiscal_year' => '2025',
            'total_amount' => 150000,
        ]);

        $this->assertDatabaseCount('budget_items', 2);
    }

    /** @test */
    public function programs_manager_can_approve_budget(): void
    {
        $budget = Budget::factory()->create([
            'status' => 'submitted',
            'created_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/budgets/{$budget->id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'status' => 'approved',
            'approved_by' => $this->programsManager->id,
        ]);

        $this->assertNotNull(Budget::find($budget->id)->approved_at);
    }

    /** @test */
    public function budget_total_cannot_exceed_donor_funding(): void
    {
        $project = Project::factory()->create(['total_budget' => 100000]);
        $project->donors()->attach($this->donor->id, [
            'funding_amount' => 100000,
        ]);

        $budgetData = [
            'project_id' => $project->id,
            'fiscal_year' => '2025',
            'items' => [
                [
                    'category' => 'Travel',
                    'allocated_amount' => 150000, // Exceeds donor funding
                ],
            ],
        ];

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(422);
    }

    /** @test */
    public function can_search_projects_by_name(): void
    {
        Project::factory()->create(['name' => 'Climate Action Project']);
        Project::factory()->create(['name' => 'Water Conservation Project']);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/projects?search=Climate');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function can_filter_projects_by_status(): void
    {
        Project::factory()->count(2)->create(['status' => 'active']);
        Project::factory()->count(3)->create(['status' => 'completed']);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/projects?status=active');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data.data')));
    }

    /** @test */
    public function project_shows_budget_utilization(): void
    {
        $project = Project::factory()->create(['total_budget' => 100000]);

        // This test is simplified as Expense model will be in Module 7
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['id', 'code', 'name']]);
    }

    /** @test */
    public function can_get_budget_categories(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/budgets/categories');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function validation_requires_project_name(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/projects', [
                'start_date' => '2025-01-01',
                'end_date' => '2026-12-31',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function validation_requires_end_date_after_start_date(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'start_date' => '2025-12-31',
                'end_date' => '2025-01-01', // Before start date
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function projects_are_paginated(): void
    {
        Project::factory()->count(30)->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/projects');

        $response->assertStatus(200);
        $this->assertEquals(25, count($response->json('data.data')));
        $this->assertArrayHasKey('links', $response->json('data'));
    }
}
