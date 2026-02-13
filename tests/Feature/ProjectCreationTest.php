<?php

namespace Tests\Feature;

use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Role $role;

    protected Donor $donor1;

    protected Donor $donor2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a Programs Manager role
        $this->role = Role::firstOrCreate(
            ['name' => 'Programs Manager'],
            ['slug' => 'programs-manager', 'description' => 'Programs Manager Role']
        );

        // Create a test user with project creation permissions
        $this->user = User::factory()->create([
            'email' => 'admin@test.com',
            'role_id' => $this->role->id,
        ]);

        // Create test donors
        $this->donor1 = Donor::factory()->create(['name' => 'Test Donor 1']);
        $this->donor2 = Donor::factory()->create(['name' => 'Test Donor 2']);
    }

    public function test_project_can_be_created_with_location(): void
    {
        $projectData = [
            'name' => 'Test Project with Location',
            'code' => 'PROJ-'.time(),
            'description' => 'Test project description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'status' => 'planning',
            'location' => 'Lusaka, Zambia',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project with Location',
            'office_location' => 'Lusaka, Zambia',
        ]);
    }

    public function test_project_can_be_created_with_donors(): void
    {
        $projectData = [
            'name' => 'Test Project with Donors',
            'code' => 'PROJ-'.time(),
            'description' => 'Test project with donor funding',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'status' => 'active',
            'location' => 'Lusaka, Zambia',
            'donors' => [
                [
                    'donor_id' => $this->donor1->id,
                    'funding_amount' => 50000.00,
                    'is_restricted' => false,
                ],
                [
                    'donor_id' => $this->donor2->id,
                    'funding_amount' => 30000.00,
                    'is_restricted' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Check that the project was created
        $project = Project::where('name', 'Test Project with Donors')->first();
        $this->assertNotNull($project);

        // Check that donors were attached with correct funding amounts
        $this->assertDatabaseHas('project_donors', [
            'project_id' => $project->id,
            'donor_id' => $this->donor1->id,
            'funding_amount' => 50000.00,
            'is_restricted' => false,
        ]);

        $this->assertDatabaseHas('project_donors', [
            'project_id' => $project->id,
            'donor_id' => $this->donor2->id,
            'funding_amount' => 30000.00,
            'is_restricted' => true,
        ]);

        // Verify donors are returned in response
        $this->assertEquals(2, count($response->json('data.donors')));
    }

    public function test_project_can_be_updated_with_location_and_donors(): void
    {
        // Create initial project
        $project = Project::factory()->create([
            'name' => 'Original Project Name',
            'office_location' => 'Ndola, Zambia',
        ]);

        // Attach one donor
        $project->donors()->attach($this->donor1->id, [
            'funding_amount' => 25000.00,
            'is_restricted' => false,
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'code' => $project->code,
            'description' => 'Updated description',
            'start_date' => $project->start_date->format('Y-m-d'),
            'end_date' => $project->end_date->format('Y-m-d'),
            'status' => 'active',
            'location' => 'Kitwe, Zambia',
            'donors' => [
                [
                    'donor_id' => $this->donor2->id,
                    'funding_amount' => 75000.00,
                    'is_restricted' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify project was updated
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'office_location' => 'Kitwe, Zambia',
        ]);

        // Verify donor was replaced
        $this->assertDatabaseMissing('project_donors', [
            'project_id' => $project->id,
            'donor_id' => $this->donor1->id,
        ]);

        $this->assertDatabaseHas('project_donors', [
            'project_id' => $project->id,
            'donor_id' => $this->donor2->id,
            'funding_amount' => 75000.00,
            'is_restricted' => true,
        ]);
    }

    public function test_project_archive_updates_status(): void
    {
        $project = Project::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/archive");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify project status was changed to cancelled
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'cancelled',
        ]);
    }
}
