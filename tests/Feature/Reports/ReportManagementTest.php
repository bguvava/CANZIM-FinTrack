<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportManagementTest extends TestCase
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
    public function unauthenticated_user_cannot_view_report_history(): void
    {
        $response = $this->getJson('/api/v1/reports');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_view_their_own_report_history(): void
    {
        Report::factory()->count(5)->create(['generated_by' => $this->programsManager->id]);
        Report::factory()->count(3)->create(['generated_by' => $this->financeOfficer->id]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/reports');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data.data');
    }

    /** @test */
    public function user_can_filter_reports_by_type(): void
    {
        Report::factory()->create([
            'generated_by' => $this->programsManager->id,
            'type' => 'budget-vs-actual',
        ]);
        Report::factory()->create([
            'generated_by' => $this->programsManager->id,
            'type' => 'cash-flow',
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/reports?report_type=budget-vs-actual');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.report_type', 'budget-vs-actual');
    }

    /** @test */
    public function user_can_filter_reports_by_status(): void
    {
        Report::factory()->create([
            'generated_by' => $this->programsManager->id,
            'status' => 'completed',
        ]);
        Report::factory()->create([
            'generated_by' => $this->programsManager->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/reports?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.status', 'completed');
    }

    /** @test */
    public function user_can_view_their_own_report_details(): void
    {
        $report = Report::factory()->create(['generated_by' => $this->programsManager->id]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson("/api/v1/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $report->id);
    }

    /** @test */
    public function user_cannot_view_other_users_reports(): void
    {
        $report = Report::factory()->create(['generated_by' => $this->financeOfficer->id]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson("/api/v1/reports/{$report->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_download_their_own_report(): void
    {
        $report = Report::factory()->completed()->create([
            'generated_by' => $this->programsManager->id,
        ]);

        Storage::disk('public')->put($report->file_path, 'PDF content');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->get("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_cannot_download_other_users_reports(): void
    {
        $report = Report::factory()->completed()->create([
            'generated_by' => $this->financeOfficer->id,
        ]);

        Storage::disk('public')->put($report->file_path, 'PDF content');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->get("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(403);
    }

    /** @test */
    public function download_returns_404_if_file_not_found(): void
    {
        $report = Report::factory()->completed()->create([
            'generated_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->get("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_delete_their_own_report(): void
    {
        $report = Report::factory()->completed()->create([
            'generated_by' => $this->programsManager->id,
        ]);

        Storage::disk('public')->put($report->file_path, 'PDF content');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->deleteJson("/api/v1/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        $this->assertFalse(Storage::disk('public')->exists($report->file_path));
    }

    /** @test */
    public function user_cannot_delete_other_users_reports(): void
    {
        $report = Report::factory()->create([
            'generated_by' => $this->financeOfficer->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->deleteJson("/api/v1/reports/{$report->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reports', ['id' => $report->id]);
    }

    /** @test */
    public function report_history_is_paginated(): void
    {
        Report::factory()->count(20)->create(['generated_by' => $this->programsManager->id]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/reports?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(10, 'data.data');
    }
}
