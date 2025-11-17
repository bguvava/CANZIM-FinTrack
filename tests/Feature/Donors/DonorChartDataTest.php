<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorChartDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $programsManagerRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $financeOfficerRole = Role::create(['name' => 'Finance Officer', 'slug' => 'finance-officer']);
        $projectOfficerRole = Role::create(['name' => 'Project Officer', 'slug' => 'project-officer']);

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

        $this->projectOfficer = User::create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345680',
            'office_location' => 'Bulawayo Office',
            'role_id' => $projectOfficerRole->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function programs_manager_can_get_chart_data()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'funding_distribution' => [
                        'labels',
                        'datasets',
                    ],
                    'top_donors' => [
                        'labels',
                        'datasets',
                    ],
                    'funding_timeline' => [
                        'labels',
                        'datasets',
                    ],
                ],
            ])
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function finance_officer_can_get_chart_data()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function project_officer_cannot_get_chart_data()
    {
        Sanctum::actingAs($this->projectOfficer);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertStatus(403);
    }

    /** @test */
    public function funding_distribution_chart_has_correct_structure()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create();
        $project = Project::factory()->create();

        $donor->projects()->attach($project->id, [
            'funding_amount' => 50000.00,
            'is_restricted' => true,
        ]);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk();

        $fundingDist = $response->json('data.funding_distribution');

        $this->assertIsArray($fundingDist['labels']);
        $this->assertContains('Restricted Funding', $fundingDist['labels']);
        $this->assertContains('Unrestricted Funding', $fundingDist['labels']);

        $this->assertIsArray($fundingDist['datasets']);
        $this->assertCount(1, $fundingDist['datasets']);
        $this->assertArrayHasKey('data', $fundingDist['datasets'][0]);
        $this->assertArrayHasKey('backgroundColor', $fundingDist['datasets'][0]);
    }

    /** @test */
    public function top_donors_chart_shows_correct_donors()
    {
        Sanctum::actingAs($this->programsManager);

        $donor1 = Donor::factory()->create(['name' => 'USAID']);
        $donor2 = Donor::factory()->create(['name' => 'World Bank']);
        $project = Project::factory()->create();

        $donor1->projects()->attach($project->id, ['funding_amount' => 100000.00]);
        $donor2->projects()->attach($project->id, ['funding_amount' => 50000.00]);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk();

        $topDonors = $response->json('data.top_donors');

        $this->assertContains('USAID', $topDonors['labels']);
        $this->assertContains('World Bank', $topDonors['labels']);

        // USAID should be first (higher funding)
        $this->assertEquals('USAID', $topDonors['labels'][0]);
    }

    /** @test */
    public function funding_timeline_chart_shows_12_months()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk();

        $timeline = $response->json('data.funding_timeline');

        $this->assertIsArray($timeline['labels']);
        $this->assertCount(12, $timeline['labels']);

        $this->assertIsArray($timeline['datasets']);
        $this->assertCount(1, $timeline['datasets']);

        $dataset = $timeline['datasets'][0];
        $this->assertEquals('Monthly Funding', $dataset['label']);
        $this->assertCount(12, $dataset['data']);
    }

    /** @test */
    public function chart_data_returns_empty_structure_when_no_data()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk();

        $data = $response->json('data');

        // Should still return proper structure even with no data
        $this->assertArrayHasKey('funding_distribution', $data);
        $this->assertArrayHasKey('top_donors', $data);
        $this->assertArrayHasKey('funding_timeline', $data);

        // Funding distribution should show 0 for both
        $fundingDist = $data['funding_distribution'];
        $this->assertEquals([0.0, 0.0], $fundingDist['datasets'][0]['data']);
    }

    /** @test */
    public function chart_data_uses_canzim_blue_color_palette()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/donors/chart-data');

        $response->assertOk();

        $data = $response->json('data');

        // Check funding distribution colors
        $colors = $data['funding_distribution']['datasets'][0]['backgroundColor'];
        $this->assertContains('#1E40AF', $colors);
        $this->assertContains('#60A5FA', $colors);

        // Check top donors color
        $topDonorsColor = $data['top_donors']['datasets'][0]['backgroundColor'];
        $this->assertEquals('#2563EB', $topDonorsColor);

        // Check timeline colors
        $timelineDataset = $data['funding_timeline']['datasets'][0];
        $this->assertEquals('#2563EB', $timelineDataset['borderColor']);
        $this->assertEquals('rgba(37, 99, 235, 0.2)', $timelineDataset['backgroundColor']);
    }
}
