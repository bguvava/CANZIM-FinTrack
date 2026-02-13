<?php

declare(strict_types=1);

namespace Tests\Feature\EnvironmentSetup;

use Tests\TestCase;

/**
 * Configuration Test
 *
 * Verifies that all configuration files are properly set up.
 *
 * Requirements: REQ-007, REQ-027A, REQ-027B, REQ-027C, REQ-027D, REQ-033, REQ-035
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test application name is correctly configured
     */
    public function test_application_name_is_configured(): void
    {
        $appName = config('app.name');

        $this->assertEquals('CANZIM FinTrack', $appName);
    }

    /**
     * Test database configuration is correct
     */
    public function test_database_configuration_is_correct(): void
    {
        $this->assertEquals('mysql', config('database.default'));
        $this->assertEquals(config('database.connections.mysql.database'), \DB::connection()->getDatabaseName());
    }

    /**
     * Test session timeout is 5 minutes
     */
    public function test_session_timeout_is_five_minutes(): void
    {
        $sessionLifetime = config('session.lifetime');

        $this->assertEquals(5, $sessionLifetime, 'Session lifetime should be 5 minutes');
    }

    /**
     * Test session driver is database (or array in testing)
     */
    public function test_session_driver_is_database(): void
    {
        $sessionDriver = config('session.driver');

        // In testing environment, session driver is 'array' (phpunit.xml)
        // In production, it should be 'database' (.env)
        $expectedDriver = (env('APP_ENV') === 'testing') ? 'array' : 'database';

        $this->assertEquals($expectedDriver, $sessionDriver);
    }

    /**
     * Test CORS configuration exists
     */
    public function test_cors_configuration_exists(): void
    {
        $corsConfig = config('cors');

        $this->assertNotNull($corsConfig);
        $this->assertIsArray($corsConfig);
        $this->assertArrayHasKey('paths', $corsConfig);
        $this->assertArrayHasKey('allowed_origins', $corsConfig);
    }

    /**
     * Test CORS allows localhost origins
     */
    public function test_cors_allows_localhost_origins(): void
    {
        $allowedOrigins = config('cors.allowed_origins');

        $this->assertContains('http://localhost', $allowedOrigins);
        $this->assertContains('http://localhost:5173', $allowedOrigins);
        $this->assertContains('http://127.0.0.1', $allowedOrigins);
    }

    /**
     * Test cache driver is configured
     */
    public function test_cache_driver_is_configured(): void
    {
        $cacheDriver = config('cache.default');

        $this->assertNotEmpty($cacheDriver);
    }

    /**
     * Test queue driver is configured
     */
    public function test_queue_driver_is_configured(): void
    {
        $queueDriver = config('queue.default');

        $this->assertNotEmpty($queueDriver);
    }
}
