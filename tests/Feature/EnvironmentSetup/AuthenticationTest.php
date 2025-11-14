<?php

declare(strict_types=1);

namespace Tests\Feature\EnvironmentSetup;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Authentication Test
 *
 * Verifies that Laravel Sanctum authentication is properly configured.
 *
 * Requirements: REQ-017
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Sanctum middleware is registered
     */
    public function test_sanctum_middleware_is_registered(): void
    {
        $this->assertTrue(class_exists(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class));
    }

    /**
     * Test authenticated user can access protected API routes
     */
    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    /**
     * Test user can logout and token is revoked
     */
    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // Logout
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
    }
}
