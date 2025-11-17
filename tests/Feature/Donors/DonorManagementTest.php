<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $programsManagerRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $financeOfficerRole = Role::create(['name' => 'Finance Officer', 'slug' => 'finance-officer']);
        $projectOfficerRole = Role::create(['name' => 'Project Officer', 'slug' => 'project-officer']);

        // Create users
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
    public function programs_manager_can_list_all_donors()
    {
        Sanctum::actingAs($this->programsManager);

        $donors = Donor::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/donors');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'contact_person',
                        'email',
                        'phone',
                        'status',
                    ],
                ],
                'pagination',
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function finance_officer_can_list_all_donors()
    {
        Sanctum::actingAs($this->financeOfficer);

        Donor::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/donors');

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function project_officer_cannot_list_donors()
    {
        Sanctum::actingAs($this->projectOfficer);

        $response = $this->getJson('/api/v1/donors');

        $response->assertStatus(403);
    }

    /** @test */
    public function can_search_donors_by_name()
    {
        Sanctum::actingAs($this->programsManager);

        Donor::factory()->create(['name' => 'USAID Foundation']);
        Donor::factory()->create(['name' => 'World Bank']);

        $response = $this->getJson('/api/v1/donors?search=USAID');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('USAID Foundation', $response->json('data.0.name'));
    }

    /** @test */
    public function can_filter_donors_by_status()
    {
        Sanctum::actingAs($this->programsManager);

        Donor::factory()->create(['status' => 'active']);
        Donor::factory()->create(['status' => 'active']);
        Donor::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/donors?status=active');

        $response->assertOk();
        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function programs_manager_can_create_donor()
    {
        Sanctum::actingAs($this->programsManager);

        $donorData = [
            'name' => 'African Union',
            'contact_person' => 'John Smith',
            'email' => 'john@au.org',
            'phone' => '+263712345678',
            'address' => '123 Main St',
            'website' => 'https://au.org',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'status'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Donor created successfully',
            ]);

        $this->assertDatabaseHas('donors', [
            'name' => 'African Union',
            'email' => 'john@au.org',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function finance_officer_cannot_create_donor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $donorData = [
            'name' => 'Test Donor',
            'email' => 'test@donor.com',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/donors', $donorData);

        $response->assertStatus(403);
    }

    /** @test */
    public function programs_manager_can_view_single_donor()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create();

        $response = $this->getJson("/api/v1/donors/{$donor->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'projects',
                    'in_kind_contributions',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => ['id' => $donor->id, 'name' => $donor->name],
            ]);
    }

    /** @test */
    public function programs_manager_can_update_donor()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@email.com',
            'status' => 'active',
        ];

        $response = $this->putJson("/api/v1/donors/{$donor->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Donor updated successfully',
            ]);

        $this->assertDatabaseHas('donors', [
            'id' => $donor->id,
            'name' => 'Updated Name',
            'email' => 'updated@email.com',
        ]);
    }

    /** @test */
    public function programs_manager_can_delete_donor_without_active_projects()
    {
        Sanctum::actingAs($this->programsManager);

        $donor = Donor::factory()->create();

        $response = $this->deleteJson("/api/v1/donors/{$donor->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Donor deleted successfully',
            ]);

        $this->assertSoftDeleted('donors', ['id' => $donor->id]);
    }

    /** @test */
    public function validation_requires_donor_name()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/donors', [
            'email' => 'test@email.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function validation_requires_valid_email()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/donors', [
            'name' => 'Test Donor',
            'donor_type' => 'government',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function donor_list_is_paginated()
    {
        Sanctum::actingAs($this->programsManager);

        Donor::factory()->count(30)->create();

        $response = $this->getJson('/api/v1/donors');

        $response->assertOk();
        $this->assertEquals(25, count($response->json('data')));
        $this->assertArrayHasKey('total', $response->json('pagination'));
        $this->assertArrayHasKey('per_page', $response->json('pagination'));
    }

    /** @test */
    public function can_get_donor_statistics()
    {
        Sanctum::actingAs($this->programsManager);

        Donor::factory()->count(5)->create(['status' => 'active']);
        Donor::factory()->count(2)->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/donors/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_donors',
                    'active_donors',
                    'total_funding',
                    'average_funding',
                ],
            ]);

        $this->assertEquals(7, $response->json('data.total_donors'));
        $this->assertEquals(5, $response->json('data.active_donors'));
    }
}
