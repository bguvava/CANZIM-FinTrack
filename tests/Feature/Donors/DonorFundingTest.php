<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorFundingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Programs Manager role
        $role = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);

        // Create user with Programs Manager role
        $this->user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_donor_creation_with_funding_total(): void
    {
        $donorData = [
            'name' => 'Test Foundation',
            'contact_person' => 'John Doe',
            'email' => 'john@testfoundation.org',
            'phone' => '+263712345678',
            'status' => 'active',
            'funding_total' => 500000.00,
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'funding_total',
                ],
            ]);

        $this->assertDatabaseHas('donors', [
            'name' => 'Test Foundation',
            'email' => 'john@testfoundation.org',
            'funding_total' => 500000.00,
        ]);

        $this->assertEquals(500000.00, $response->json('data.funding_total'));
    }

    public function test_donor_creation_without_funding_total(): void
    {
        $donorData = [
            'name' => 'Test Foundation',
            'contact_person' => 'John Doe',
            'email' => 'john@testfoundation.org',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(201);

        $donor = Donor::where('email', 'john@testfoundation.org')->first();

        // Database has DEFAULT 0 for funding_total, so it should be 0.00 when not provided
        $this->assertEquals('0.00', $donor->funding_total);
    }

    public function test_donor_update_includes_funding_total(): void
    {
        $donor = Donor::factory()->create([
            'funding_total' => 100000.00,
        ]);

        $updateData = [
            'name' => $donor->name,
            'funding_total' => 250000.00,
        ];

        $response = $this->putJson("/api/v1/donors/{$donor->id}", $updateData);

        $response->assertStatus(200);

        $donor->refresh();

        $this->assertEquals(250000.00, $donor->funding_total);
    }

    public function test_donor_list_shows_total_funding_from_projects(): void
    {
        $donor = Donor::factory()->create([
            'funding_total' => 100000.00,
        ]);

        // Create projects with funding
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $donor->projects()->attach($project1->id, [
            'funding_amount' => 30000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $donor->projects()->attach($project2->id, [
            'funding_amount' => 45000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => true,
        ]);

        $response = $this->getJson('/api/v1/donors');

        $response->assertStatus(200);

        $donorData = collect($response->json('data'))->firstWhere('id', $donor->id);

        // total_funding should be sum from project_donors pivot (75000)
        $this->assertEquals(75000.00, $donorData['total_funding']);

        // funding_total should be the static value from donors table (100000)
        $this->assertEquals(100000.00, $donorData['funding_total']);
    }

    public function test_donor_resource_includes_funding_breakdown(): void
    {
        $donor = Donor::factory()->create();

        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $donor->projects()->attach($project1->id, [
            'funding_amount' => 50000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => true,
        ]);

        $donor->projects()->attach($project2->id, [
            'funding_amount' => 30000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $response = $this->getJson("/api/v1/donors/{$donor->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'total_funding',
                    'restricted_funding',
                    'unrestricted_funding',
                ],
            ]);

        $data = $response->json('data');

        $this->assertEquals(80000.00, $data['total_funding']);
        $this->assertEquals(50000.00, $data['restricted_funding']);
        $this->assertEquals(30000.00, $data['unrestricted_funding']);
    }

    public function test_funding_total_validation_rejects_negative_values(): void
    {
        $donorData = [
            'name' => 'Test Foundation',
            'email' => 'test@foundation.org',
            'funding_total' => -1000.00,
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['funding_total']);
    }

    public function test_funding_total_validation_accepts_zero(): void
    {
        $donorData = [
            'name' => 'Test Foundation',
            'email' => 'test@foundation.org',
            'funding_total' => 0.00,
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('donors', [
            'email' => 'test@foundation.org',
            'funding_total' => 0.00,
        ]);
    }

    public function test_funding_total_validation_rejects_non_numeric(): void
    {
        $donorData = [
            'name' => 'Test Foundation',
            'email' => 'test@foundation.org',
            'funding_total' => 'not-a-number',
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['funding_total']);
    }

    public function test_donor_statistics_include_funding_totals(): void
    {
        // Create donors with various funding amounts
        $donor1 = Donor::factory()->create(['funding_total' => 100000.00]);
        $donor2 = Donor::factory()->create(['funding_total' => 250000.00]);
        $donor3 = Donor::factory()->create(['funding_total' => 0.00]); // Database has DEFAULT 0, not nullable

        $project = Project::factory()->create();

        // Assign project-specific funding
        $donor1->projects()->attach($project->id, [
            'funding_amount' => 50000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $donor2->projects()->attach($project->id, [
            'funding_amount' => 75000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $response = $this->getJson('/api/v1/donors');

        $response->assertStatus(200);

        // Verify that total_funding is calculated from project assignments
        $donors = collect($response->json('data'));

        $donor1Data = $donors->firstWhere('id', $donor1->id);
        $donor2Data = $donors->firstWhere('id', $donor2->id);
        $donor3Data = $donors->firstWhere('id', $donor3->id);

        $this->assertEquals(50000.00, $donor1Data['total_funding']);
        $this->assertEquals(75000.00, $donor2Data['total_funding']);
        $this->assertEquals(0.00, $donor3Data['total_funding']);
    }

    public function test_donor_list_shows_zero_funding_when_no_projects_assigned(): void
    {
        $donor = Donor::factory()->create([
            'funding_total' => 500000.00,
        ]);

        $response = $this->getJson('/api/v1/donors');

        $response->assertStatus(200);

        $donorData = collect($response->json('data'))->firstWhere('id', $donor->id);

        // total_funding should be 0 (no project assignments)
        $this->assertEquals(0.00, $donorData['total_funding']);

        // But funding_total should still show the static commitment
        $this->assertEquals(500000.00, $donorData['funding_total']);
    }
}
