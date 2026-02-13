<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $role = Role::factory()->create(['name' => 'Programs Manager']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        $this->otherUser = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_user_can_view_their_report_history(): void
    {
        Report::factory()->count(3)->create(['generated_by' => $this->user->id]);
        Report::factory()->count(2)->create(['generated_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/history');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_user_can_filter_report_history_by_type(): void
    {
        Report::factory()->create([
            'generated_by' => $this->user->id,
            'type' => 'budget-vs-actual',
        ]);
        Report::factory()->create([
            'generated_by' => $this->user->id,
            'type' => 'cash-flow',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/history?report_type=budget-vs-actual');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.report_type', 'budget-vs-actual');
    }

    public function test_user_can_download_report_from_history(): void
    {
        $filePath = 'reports/test-report.pdf';
        Storage::disk('public')->put($filePath, 'PDF content');

        $report = Report::factory()->create([
            'generated_by' => $this->user->id,
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(200);
    }

    public function test_user_cannot_download_others_reports(): void
    {
        $filePath = 'reports/other-user-report.pdf';
        Storage::disk('public')->put($filePath, 'PDF content');

        $report = Report::factory()->create([
            'generated_by' => $this->otherUser->id,
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(403);
    }

    public function test_download_returns_404_if_file_not_found(): void
    {
        $report = Report::factory()->create([
            'generated_by' => $this->user->id,
            'file_path' => 'reports/nonexistent.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/reports/{$report->id}/pdf");

        $response->assertStatus(404);
    }

    public function test_user_can_filter_report_history_by_date_range(): void
    {
        Report::factory()->create([
            'generated_by' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);
        Report::factory()->create([
            'generated_by' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/history');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_reports_are_ordered_by_created_at_desc(): void
    {
        $oldReport = Report::factory()->create([
            'generated_by' => $this->user->id,
            'created_at' => now()->subDays(5),
        ]);
        $newReport = Report::factory()->create([
            'generated_by' => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/history');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertEquals($newReport->id, $data[0]['id']);
        $this->assertEquals($oldReport->id, $data[1]['id']);
    }
}
