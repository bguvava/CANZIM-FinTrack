<?php

declare(strict_types=1);

namespace Tests\Feature\EnvironmentSetup;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * API Routes Test
 *
 * Verifies that API routes are properly configured and accessible.
 *
 * Requirements: REQ-017, REQ-035
 */
class ApiRoutesTest extends TestCase
{
    /**
     * Test API health check endpoint
     *
     * @return void
     */
    public function test_api_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'version',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'success',
                'version' => '1.0.0',
            ]);
    }

    /**
     * Test API returns JSON format
     *
     * @return void
     */
    public function test_api_returns_json_format(): void
    {
        $response = $this->getJson('/api/v1/health');

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Test API base path is accessible
     *
     * @return void
     */
    public function test_api_base_path_is_accessible(): void
    {
        // The health endpoint should be accessible without authentication
        $response = $this->getJson('/api/v1/health');

        $response->assertSuccessful();
    }
}
