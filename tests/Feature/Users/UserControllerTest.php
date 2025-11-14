<?php

namespace Tests\Feature\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Role $programsManagerRole;

    protected Role $financeOfficerRole;

    protected Role $projectOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        // Create test users
        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $this->programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);

        $this->projectOfficer = User::create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345680',
            'office_location' => 'Bulawayo Office',
            'role_id' => $this->projectOfficerRole->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function programs_manager_can_list_all_users()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'office_location',
                        'avatar_path',
                        'avatar_url',
                        'initials',
                        'status',
                        'role' => ['id', 'name', 'slug'],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta',
            ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function non_programs_manager_cannot_list_users()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/users');

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users()
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    /** @test */
    public function can_filter_users_by_search()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users?search=Finance');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Finance Officer', $response->json('data.0.name'));
    }

    /** @test */
    public function can_filter_users_by_role()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users?role_id='.$this->projectOfficerRole->id);

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Project Officer', $response->json('data.0.name'));
    }

    /** @test */
    public function can_filter_users_by_status()
    {
        $this->financeOfficer->update(['status' => 'inactive']);
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users?status=inactive');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Finance Officer', $response->json('data.0.name'));
    }

    /** @test */
    public function can_filter_users_by_office_location()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users?office_location=Bulawayo Office');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Project Officer', $response->json('data.0.name'));
    }

    /** @test */
    public function programs_manager_can_create_user()
    {
        Sanctum::actingAs($this->programsManager);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'phone_number' => '+263712345681',
            'office_location' => 'Mutare Office',
            'role_id' => $this->financeOfficerRole->id,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertCreated()
            ->assertJson([
                'status' => 'success',
                'message' => 'User created successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
        ]);

        // Check activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'activity_type' => 'user_created',
            'description' => 'Created new user: New User',
        ]);
    }

    /** @test */
    public function create_user_validates_required_fields()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/users', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'role_id', 'password']);
    }

    /** @test */
    public function create_user_validates_email_uniqueness()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'Duplicate User',
            'email' => $this->financeOfficer->email,
            'role_id' => $this->financeOfficerRole->id,
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function create_user_validates_password_requirements()
    {
        Sanctum::actingAs($this->programsManager);

        // Password too short
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role_id' => $this->financeOfficerRole->id,
            'password' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        // Password without letters
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role_id' => $this->financeOfficerRole->id,
            'password' => '12345678',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        // Password without numbers
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role_id' => $this->financeOfficerRole->id,
            'password' => 'abcdefgh',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function non_programs_manager_cannot_create_user()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'role_id' => $this->financeOfficerRole->id,
            'password' => 'password123',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function programs_manager_can_view_user_details()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson("/api/v1/users/{$this->financeOfficer->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $this->financeOfficer->id,
                    'name' => 'Finance Officer',
                    'email' => 'fo@test.com',
                ],
            ]);
    }

    /** @test */
    public function user_can_view_own_details()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson("/api/v1/users/{$this->financeOfficer->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $this->financeOfficer->id,
                    'email' => 'fo@test.com',
                ],
            ]);
    }

    /** @test */
    public function user_cannot_view_other_user_details()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson("/api/v1/users/{$this->projectOfficer->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function programs_manager_can_update_user()
    {
        Sanctum::actingAs($this->programsManager);

        $updateData = [
            'name' => 'Updated Finance Officer',
            'email' => 'updated-fo@test.com',
            'phone_number' => '+263712999999',
            'office_location' => 'Bulawayo Office',
            'role_id' => $this->financeOfficerRole->id,
        ];

        $response = $this->putJson("/api/v1/users/{$this->financeOfficer->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'User updated successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->financeOfficer->id,
            'name' => 'Updated Finance Officer',
            'email' => 'updated-fo@test.com',
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'activity_type' => 'user_updated',
        ]);
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        Sanctum::actingAs($this->financeOfficer);

        $updateData = [
            'name' => 'Self Updated Name',
            'email' => $this->financeOfficer->email,
            'phone_number' => '+263712888888',
            'office_location' => 'Mutare Office',
            'role_id' => $this->financeOfficer->role_id,
        ];

        $response = $this->putJson("/api/v1/users/{$this->financeOfficer->id}", $updateData);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $this->financeOfficer->id,
            'name' => 'Self Updated Name',
        ]);
    }

    /** @test */
    public function update_user_validates_email_uniqueness()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->putJson("/api/v1/users/{$this->financeOfficer->id}", [
            'name' => 'Finance Officer',
            'email' => $this->projectOfficer->email, // Try to use another user's email
            'role_id' => $this->financeOfficerRole->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function programs_manager_can_deactivate_user()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson("/api/v1/users/{$this->financeOfficer->id}/deactivate");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'User deactivated successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->financeOfficer->id,
            'status' => 'inactive',
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'activity_type' => 'user_deactivated',
        ]);
    }

    /** @test */
    public function programs_manager_cannot_deactivate_self(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson("/api/v1/users/{$this->programsManager->id}/deactivate");

        $response->assertForbidden();
    }

    /** @test */
    public function programs_manager_can_activate_user()
    {
        $this->financeOfficer->update(['status' => 'inactive']);
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson("/api/v1/users/{$this->financeOfficer->id}/activate");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'User activated successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->financeOfficer->id,
            'status' => 'active',
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'activity_type' => 'user_activated',
        ]);
    }

    /** @test */
    public function non_programs_manager_cannot_deactivate_user()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->postJson("/api/v1/users/{$this->projectOfficer->id}/deactivate");

        $response->assertForbidden();
    }

    /** @test */
    public function programs_manager_can_delete_user()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->deleteJson("/api/v1/users/{$this->financeOfficer->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'User deleted successfully.',
            ]);

        // Check soft delete
        $this->assertSoftDeleted('users', [
            'id' => $this->financeOfficer->id,
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'activity_type' => 'user_deleted',
        ]);
    }

    /** @test */
    public function programs_manager_cannot_delete_self()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->deleteJson("/api/v1/users/{$this->programsManager->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function non_programs_manager_cannot_delete_user()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->deleteJson("/api/v1/users/{$this->projectOfficer->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function can_get_roles_list()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users/roles/list');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function can_get_office_locations_list()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/users/locations/list');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);

        $this->assertContains('Harare Office', $response->json('data'));
        $this->assertContains('Bulawayo Office', $response->json('data'));
    }
}
