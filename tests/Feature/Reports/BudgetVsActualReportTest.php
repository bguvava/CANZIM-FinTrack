<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Project;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BudgetVsActualReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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
    }

    /** @test */
    public function unauthenticated_user_cannot_generate_budget_vs_actual_report(): void
    {
        $response = $this->postJson('/api/v1/reports/budget-vs-actual', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function project_officer_cannot_generate_budget_vs_actual_report(): void
    {
        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function programs_manager_can_generate_budget_vs_actual_report(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.report_type', 'budget-vs-actual')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'report_type',
                    'title',
                    'parameters',
                    'file_path',
                    'file_url',
                    'status',
                    'generated_by',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('reports', [
            'type' => 'budget-vs-actual',
            'generated_by' => $this->programsManager->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function finance_officer_can_generate_budget_vs_actual_report(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function budget_vs_actual_report_validates_date_range(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2025-12-31',
                'end_date' => '2025-01-01',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function budget_vs_actual_report_validates_future_dates(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function budget_vs_actual_report_can_filter_by_projects(): void
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'project_ids' => [$project1->id, $project2->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $report = Report::latest()->first();
        $this->assertCount(2, $report->parameters['project_ids']);
    }

    /** @test */
    public function budget_vs_actual_report_limits_project_filters_to_five(): void
    {
        $projects = Project::factory()->count(6)->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'project_ids' => $projects->pluck('id')->toArray(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_ids']);
    }

    /** @test */
    public function budget_vs_actual_report_generates_pdf_file(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertNotNull($report->file_path);
        $this->assertTrue(Storage::disk('public')->exists($report->file_path));
    }

    /** @test */
    public function budget_vs_actual_report_requires_date_range(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/budget-vs-actual', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }
}
