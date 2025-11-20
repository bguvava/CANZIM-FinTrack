<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashFlowReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

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
    public function unauthenticated_user_cannot_generate_cash_flow_report(): void
    {
        $response = $this->postJson('/api/v1/reports/cash-flow', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function project_officer_cannot_generate_cash_flow_report(): void
    {
        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function programs_manager_can_generate_cash_flow_report(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.report_type', 'cash-flow')
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('reports', [
            'type' => 'cash-flow',
            'generated_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function finance_officer_can_generate_cash_flow_report(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function cash_flow_report_can_group_by_month(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'grouping' => 'month',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('month', $report->parameters['grouping']);
    }

    /** @test */
    public function cash_flow_report_can_group_by_quarter(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'grouping' => 'quarter',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('quarter', $report->parameters['grouping']);
    }

    /** @test */
    public function cash_flow_report_validates_grouping_values(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'grouping' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grouping']);
    }

    /** @test */
    public function cash_flow_report_generates_pdf_file(): void
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/reports/cash-flow', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);

        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertNotNull($report->file_path);
        $this->assertTrue(Storage::disk('public')->exists($report->file_path));
    }
}
