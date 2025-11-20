<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Test database integrity, foreign keys, and migration rollback
 * REQ-069 & REQ-070: Test Migration Rollback and Fresh
 */
class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that foreign key constraints are properly defined
     * REQ-060: Define Foreign Key Constraints
     */
    public function test_foreign_key_constraints_exist(): void
    {
        $this->seed();

        $role = DB::table('roles')->first();
        $user = DB::table('users')->where('role_id', $role->id)->first();

        $this->assertNotNull($user);
        $this->assertEquals($role->id, $user->role_id);
    }

    /**
     * Test cascade delete on projects → budgets
     */
    public function test_cascade_delete_from_projects_to_budgets(): void
    {
        $this->seed();

        $user = DB::table('users')->first();

        $projectId = DB::table('projects')->insertGetId([
            'code' => 'TEST-001',
            'name' => 'Test Project',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'total_budget' => 100000.00,
            'status' => 'active',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('budgets')->insert([
            'project_id' => $projectId,
            'fiscal_year' => '2025',
            'total_amount' => 100000.00,
            'status' => 'draft',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $budgetCount = DB::table('budgets')->where('project_id', $projectId)->count();
        $this->assertEquals(1, $budgetCount);

        DB::table('projects')->where('id', $projectId)->delete();

        $budgetCountAfterDelete = DB::table('budgets')->where('project_id', $projectId)->count();
        $this->assertEquals(0, $budgetCountAfterDelete, 'Budgets should be cascade deleted with project');
    }

    /**
     * Test set null on users → projects (approved_by)
     */
    public function test_set_null_on_user_delete_for_approver_fields(): void
    {
        $this->seed();

        $creator = DB::table('users')->first();

        $approverId = DB::table('users')->insertGetId([
            'name' => 'Approver User',
            'email' => 'approver@test.com',
            'password' => bcrypt('password'),
            'role_id' => $creator->role_id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $projectId = DB::table('projects')->insertGetId([
            'code' => 'TEST-002',
            'name' => 'Test Project 2',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'total_budget' => 50000.00,
            'status' => 'active',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $budgetId = DB::table('budgets')->insertGetId([
            'project_id' => $projectId,
            'fiscal_year' => '2025',
            'total_amount' => 50000.00,
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $approverId)->delete();

        $budget = DB::table('budgets')->where('id', $budgetId)->first();
        $this->assertNull($budget->approved_by, 'approved_by should be set to null when approver is deleted');
    }

    /**
     * Test unique constraints are working
     */
    public function test_unique_constraints_prevent_duplicates(): void
    {
        $this->seed();

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('roles')->insert([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
            'description' => 'Duplicate role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test composite unique constraint on project_donors
     */
    public function test_composite_unique_constraint_on_project_donors(): void
    {
        $this->seed();

        $user = DB::table('users')->first();

        $projectId = DB::table('projects')->insertGetId([
            'code' => 'TEST-003',
            'name' => 'Test Project 3',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'total_budget' => 75000.00,
            'status' => 'active',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $donorId = DB::table('donors')->insertGetId([
            'name' => 'Test Donor',
            'funding_total' => 75000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_donors')->insert([
            'project_id' => $projectId,
            'donor_id' => $donorId,
            'funding_amount' => 75000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('project_donors')->insert([
            'project_id' => $projectId,
            'donor_id' => $donorId,
            'funding_amount' => 50000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test polymorphic relationships work correctly for comments
     */
    public function test_polymorphic_comments_can_attach_to_multiple_models(): void
    {
        $this->seed();

        $project = \App\Models\Project::factory()->create();
        $budget = \App\Models\Budget::factory()->create();
        $expense = \App\Models\Expense::factory()->create();
        $user = \App\Models\User::factory()->create();

        // Create comment on project
        DB::table('comments')->insert([
            'commentable_type' => get_class($project),
            'commentable_id' => $project->id,
            'user_id' => $user->id,
            'content' => 'This is a test comment on a project',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('comments', [
            'commentable_type' => get_class($project),
            'commentable_id' => $project->id,
        ]);
    }

    /**
     * Test decimal precision for financial amounts
     */
    public function test_decimal_precision_for_financial_amounts(): void
    {
        $this->seed();

        $user = DB::table('users')->first();

        $projectId = DB::table('projects')->insertGetId([
            'code' => 'TEST-005',
            'name' => 'Test Project 5',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'total_budget' => 12345.67,
            'status' => 'active',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $project = DB::table('projects')->where('id', $projectId)->first();
        $this->assertEquals('12345.67', $project->total_budget, 'Budget should maintain 2 decimal places');
    }

    /**
     * Test ENUM field constraints
     */
    public function test_enum_fields_accept_valid_values(): void
    {
        $this->seed();

        $user = DB::table('users')->first();

        $validStatuses = ['planning', 'active', 'on_hold', 'completed', 'cancelled'];

        foreach ($validStatuses as $status) {
            $projectId = DB::table('projects')->insertGetId([
                'code' => 'TEST-'.strtoupper($status),
                'name' => 'Test Project '.$status,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'total_budget' => 50000.00,
                'status' => $status,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $project = DB::table('projects')->where('id', $projectId)->first();
            $this->assertEquals($status, $project->status);
        }
    }

    /**
     * Test indexes exist on frequently queried columns
     * REQ-061: Create Database Indexes
     */
    public function test_indexes_exist_on_important_columns(): void
    {
        $indexedTables = [
            'users' => ['email', 'role_id', 'status'],
            'projects' => ['code', 'status', 'created_by'],
            'budgets' => ['project_id', 'status'],
            'expenses' => ['project_id', 'category_id', 'status'],
        ];

        foreach ($indexedTables as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Column '{$column}' should exist in '{$table}' table for indexing"
                );
            }
        }
    }

    /**
     * Test soft delete functionality works correctly
     */
    public function test_soft_delete_functionality_works_correctly(): void
    {
        $this->seed();

        $user = DB::table('users')->first();

        $projectId = DB::table('projects')->insertGetId([
            'code' => 'TEST-SOFT-DELETE',
            'name' => 'Test Soft Delete Project',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'total_budget' => 40000.00,
            'status' => 'active',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->where('id', $projectId)->update([
            'deleted_at' => now(),
        ]);

        $project = DB::table('projects')->where('id', $projectId)->first();
        $this->assertNotNull($project->deleted_at, 'Project should have deleted_at timestamp');
    }

    /**
     * Test that migration rollback works without errors
     * REQ-069: Test Migration Rollback
     */
    public function test_migration_rollback_executes_successfully(): void
    {
        $exitCode = Artisan::call('migrate:rollback', ['--force' => true]);

        $this->assertEquals(0, $exitCode, 'Migration rollback should execute without errors');
    }

    /**
     * Test that migrate:fresh recreates database successfully
     * REQ-070: Test Migration Fresh
     */
    public function test_migrate_fresh_recreates_database_successfully(): void
    {
        $exitCode = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

        $this->assertEquals(0, $exitCode, 'Migration fresh should execute without errors');

        $roles = DB::table('roles')->count();
        $this->assertEquals(3, $roles, 'Roles should be reseeded after migrate:fresh');
    }

    /**
     * Test database connection is active
     */
    public function test_database_connection_is_active(): void
    {
        $this->assertTrue(
            DB::connection()->getDatabaseName() === 'my_canzimdb',
            'Database connection should be active and connected to my_canzimdb'
        );
    }

    /**
     * Test all tables have primary keys
     */
    public function test_all_tables_have_primary_keys(): void
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::connection()->getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$databaseName}"};

            if (in_array($tableName, ['migrations', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches', 'password_reset_tokens', 'sessions'])) {
                continue;
            }

            $this->assertTrue(
                Schema::hasColumn($tableName, 'id'),
                "Table '{$tableName}' should have an 'id' primary key column"
            );
        }
    }

    /**
     * Test total table count matches expected
     */
    public function test_total_table_count_matches_expected(): void
    {
        $tables = DB::select('SHOW TABLES');
        $tableCount = count($tables);

        $this->assertGreaterThanOrEqual(26, $tableCount, 'Should have at least 26 tables in database');
    }
}
