<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseSummaryReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Project $project;

    protected ExpenseCategory $category;

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

        // Create project and category
        $this->project = Project::factory()->create();
        $this->category = ExpenseCategory::factory()->create();
    }

    /** @test */
    public function unauthenticated_user_cannot_generate_expense_summary_report(): void
    {
        $response = $this->postJson('/api/v1/reports/expense-summary', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function project_officer_cannot_generate_expense_summary_report(): void
    {
        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function programs_manager_can_generate_expense_summary_report(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.report_type', 'expense-summary');

        $this->assertDatabaseHas('reports', [
            'type' => 'expense-summary',
            'generated_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function finance_officer_can_generate_expense_summary_report(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function expense_summary_report_can_group_by_category(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'group_by' => 'category',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('category', $report->parameters['group_by']);
    }

    /** @test */
    public function expense_summary_report_can_group_by_project(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'group_by' => 'project',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('project', $report->parameters['group_by']);
    }

    /** @test */
    public function expense_summary_report_can_filter_by_categories(): void
    {
        $category2 = ExpenseCategory::factory()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'category_ids' => [$this->category->id, $category2->id],
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertCount(2, $report->parameters['category_ids']);
    }

    /** @test */
    public function expense_summary_report_limits_category_filters_to_five(): void
    {
        $categories = ExpenseCategory::factory()->count(6)->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'category_ids' => $categories->pluck('id')->toArray(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_ids']);
    }

    /** @test */
    public function expense_summary_report_generates_pdf_file(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/expense-summary', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertNotNull($report->file_path);
        $this->assertTrue(Storage::disk('public')->exists($report->file_path));
    }
}
