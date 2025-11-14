<?php

declare(strict_types=1);

namespace Tests\Feature\EnvironmentSetup;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Database Connection Test
 *
 * Verifies that the database connection is properly configured
 * and the correct database is being used.
 *
 * Requirements: REQ-007, REQ-008
 */
class DatabaseConnectionTest extends TestCase
{
    /**
     * Test database connection is successful
     */
    public function test_database_connection_is_successful(): void
    {
        // Attempt to connect to the database
        $connected = true;

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $connected = false;
        }

        $this->assertTrue($connected, 'Database connection failed');
    }

    /**
     * Test database name is correct
     */
    public function test_database_name_is_correct(): void
    {
        $databaseName = DB::connection()->getDatabaseName();

        $this->assertEquals('my_canzimdb', $databaseName, 'Database name does not match expected value');
    }

    /**
     * Test database tables exist
     */
    public function test_database_tables_exist(): void
    {
        // Check if migrations table exists
        $hasUsersTable = DB::getSchemaBuilder()->hasTable('users');
        $hasCacheTable = DB::getSchemaBuilder()->hasTable('cache');
        $hasJobsTable = DB::getSchemaBuilder()->hasTable('jobs');
        $hasSessionsTable = DB::getSchemaBuilder()->hasTable('sessions');

        $this->assertTrue($hasUsersTable, 'Users table does not exist');
        $this->assertTrue($hasCacheTable, 'Cache table does not exist');
        $this->assertTrue($hasJobsTable, 'Jobs table does not exist');
        $this->assertTrue($hasSessionsTable, 'Sessions table does not exist');
    }
}
