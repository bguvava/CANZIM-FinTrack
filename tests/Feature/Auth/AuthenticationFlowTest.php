<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Authentication Flow Test
 *
 * Tests the complete authentication flow including:
 * - Login with CSRF token
 * - Token attachment to API requests
 * - Session expiry and 401 handling
 * - Password verification for session unlock
 *
 * Requirements: REQ-017 - Authentication and Authorization
 */
class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can login successfully and receive a token
     */
    public function test_user_can_login_successfully(): void
    {
        // Create a role first
        $role = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'role' => [
                            'id',
                            'name',
                            'slug',
                        ],
                    ],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test authenticated requests include Bearer token
     */
    public function test_authenticated_requests_include_bearer_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                ],
            ]);
    }

    /**
     * Test API request without token returns 401
     */
    public function test_api_request_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/profile');

        $response->assertStatus(401);
    }

    /**
     * Test API request with invalid token returns 401
     */
    public function test_api_request_with_invalid_token_returns_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-12345',
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/profile');

        $response->assertStatus(401);
    }

    /**
     * Test password verification for session unlock
     */
    public function test_password_verification_for_session_unlock(): void
    {
        $role = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password verified',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                ],
            ]);
    }

    /**
     * Test password verification fails with wrong password
     */
    public function test_password_verification_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-password', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid password',
            ]);
    }

    /**
     * Test user can logout successfully
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);

        // Token should be revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test user can access profile endpoint
     */
    public function test_user_can_access_profile_endpoint(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'role',
                        'last_login_at',
                        'created_at',
                    ],
                ],
            ]);
    }

    /**
     * Test multiple API endpoints require authentication
     */
    public function test_multiple_api_endpoints_require_authentication(): void
    {
        // Note: /api/v1/auth/logout is POST-only, so we test it separately
        $getEndpoints = [
            '/api/v1/auth/profile',
            '/api/v1/projects',
            '/api/v1/budgets',
            '/api/v1/expenses',
            '/api/v1/donors',
        ];

        foreach ($getEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401, "Endpoint {$endpoint} should require authentication");
        }

        // Test POST logout endpoint separately
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(401, 'Endpoint /api/v1/auth/logout should require authentication');
    }

    /**
     * Test token persists across multiple requests
     */
    public function test_token_persists_across_multiple_requests(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // First request
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/profile');

        $response1->assertStatus(200);

        // Second request with same token
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/profile');

        $response2->assertStatus(200);
    }

    /**
     * Test CSRF cookie endpoint is accessible
     */
    public function test_csrf_cookie_endpoint_is_accessible(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertStatus(204);
    }

    /**
     * Test login validates required fields
     */
    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login validates email format
     */
    public function test_login_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
