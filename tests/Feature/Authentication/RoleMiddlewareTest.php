<?php

namespace Tests\Feature\Authentication;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Full system access',
        ]);

        Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
            'description' => 'Financial operations',
        ]);

        Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
            'description' => 'Project implementation',
        ]);

        // Create test routes
        Route::middleware(['auth:sanctum', 'role:programs-manager'])
            ->get('/test/programs-manager-only', function () {
                return response()->json(['message' => 'Programs Manager access granted']);
            });

        Route::middleware(['auth:sanctum', 'role:finance-officer,programs-manager'])
            ->get('/test/finance-access', function () {
                return response()->json(['message' => 'Finance access granted']);
            });

        Route::middleware(['auth:sanctum', 'role:project-officer'])
            ->get('/test/project-officer-only', function () {
                return response()->json(['message' => 'Project Officer access granted']);
            });
    }

    public function test_programs_manager_can_access_programs_manager_route(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/test/programs-manager-only');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Programs Manager access granted']);
    }

    public function test_finance_officer_cannot_access_programs_manager_route(): void
    {
        $role = Role::where('slug', 'finance-officer')->first();

        $user = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/test/programs-manager-only');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You do not have permission to access this resource',
            ]);
    }

    public function test_project_officer_cannot_access_programs_manager_route(): void
    {
        $role = Role::where('slug', 'project-officer')->first();

        $user = User::create([
            'name' => 'Project Officer',
            'email' => 'po@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/test/programs-manager-only');

        $response->assertStatus(403);
    }

    public function test_finance_officer_can_access_finance_route(): void
    {
        $role = Role::where('slug', 'finance-officer')->first();

        $user = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/test/finance-access');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Finance access granted']);
    }

    public function test_programs_manager_can_access_finance_route(): void
    {
        $role = Role::where('slug', 'programs-manager')->first();

        $user = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@canzim.org',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/test/finance-access');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/test/programs-manager-only');

        $response->assertStatus(401);
    }

    public function test_middleware_returns_401_for_missing_auth_token(): void
    {
        $response = $this->getJson('/test/project-officer-only');

        $response->assertStatus(401);
    }
}
