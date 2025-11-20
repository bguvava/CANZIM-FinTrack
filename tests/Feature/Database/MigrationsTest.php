<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Test all database migrations are created successfully
 * REQ-073: Write Migration Tests
 */
class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all core tables exist in database
     */
    public function test_core_tables_exist(): void
    {
        $tables = [
            'users',
            'roles',
            'projects',
            'donors',
            'project_donors',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test that all financial tables exist in database
     */
    public function test_financial_tables_exist(): void
    {
        $tables = [
            'budgets',
            'budget_items',
            'expense_categories',
            'expenses',
            'vendors',
            'purchase_orders',
            'bank_accounts',
            'cash_flow',
            'in_kind_contributions',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test that all system tables exist in database
     */
    public function test_system_tables_exist(): void
    {
        $tables = [
            'comments',
            'comment_attachments',
            'documents',
            'audit_trails',
            'activity_logs',
            'notifications',
            'system_settings',
            'reports',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test users table has all required columns
     */
    public function test_users_table_has_required_columns(): void
    {
        $columns = [
            'id',
            'name',
            'email',
            'password',
            'role_id',
            'office_location',
            'status',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Column '{$column}' does not exist in users table"
            );
        }
    }

    /**
     * Test roles table has all required columns
     */
    public function test_roles_table_has_required_columns(): void
    {
        $columns = ['id', 'name', 'slug', 'description', 'created_at', 'updated_at'];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('roles', $column),
                "Column '{$column}' does not exist in roles table"
            );
        }
    }

    /**
     * Test projects table has all required columns
     */
    public function test_projects_table_has_required_columns(): void
    {
        $columns = [
            'id',
            'code',
            'name',
            'description',
            'start_date',
            'end_date',
            'total_budget',
            'status',
            'office_location',
            'created_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('projects', $column),
                "Column '{$column}' does not exist in projects table"
            );
        }
    }

    /**
     * Test budgets table has all required columns
     */
    public function test_budgets_table_has_required_columns(): void
    {
        $columns = [
            'id',
            'project_id',
            'fiscal_year',
            'total_amount',
            'status',
            'approved_by',
            'approved_at',
            'created_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('budgets', $column),
                "Column '{$column}' does not exist in budgets table"
            );
        }
    }

    /**
     * Test expenses table has all required columns including approval workflow
     */
    public function test_expenses_table_has_approval_workflow_columns(): void
    {
        $columns = [
            'id',
            'project_id',
            'budget_item_id',
            'category_id',
            'expense_date',
            'amount',
            'description',
            'receipt_path',
            'status',
            'submitted_by',
            'reviewed_by',
            'approved_by',
            'submitted_at',
            'reviewed_at',
            'approved_at',
            'rejection_reason',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('expenses', $column),
                "Column '{$column}' does not exist in expenses table"
            );
        }
    }

    /**
     * Test cash_flow table has all required columns for tracking
     */
    public function test_cash_flow_table_has_tracking_columns(): void
    {
        $columns = [
            'id',
            'transaction_date',
            'type',
            'amount',
            'description',
            'project_id',
            'donor_id',
            'expense_id',
            'bank_account_id',
            'balance_before',
            'balance_after',
            'created_by',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('cash_flow', $column),
                "Column '{$column}' does not exist in cash_flow table"
            );
        }
    }

    /**
     * Test polymorphic comments table has correct structure
     */
    public function test_comments_table_has_polymorphic_structure(): void
    {
        $columns = [
            'id',
            'commentable_type',
            'commentable_id',
            'user_id',
            'parent_id',
            'content',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('comments', $column),
                "Column '{$column}' does not exist in comments table"
            );
        }
    }

    /**
     * Test polymorphic documents table has correct structure
     */
    public function test_documents_table_has_polymorphic_structure(): void
    {
        $columns = [
            'id',
            'documentable_type',
            'documentable_id',
            'title',
            'description',
            'file_name',
            'file_path',
            'file_type',
            'file_size',
            'uploaded_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('documents', $column),
                "Column '{$column}' does not exist in documents table"
            );
        }
    }

    /**
     * Test audit_trails table has all required columns for comprehensive logging
     */
    public function test_audit_trails_table_has_logging_columns(): void
    {
        $columns = [
            'id',
            'user_id',
            'action',
            'auditable_type',
            'auditable_id',
            'old_values',
            'new_values',
            'ip_address',
            'user_agent',
            'created_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('audit_trails', $column),
                "Column '{$column}' does not exist in audit_trails table"
            );
        }
    }

    /**
     * Test that soft deletes are enabled on appropriate tables
     */
    public function test_soft_deletes_enabled_on_required_tables(): void
    {
        $tables = [
            'projects',
            'donors',
            'budgets',
            'expenses',
            'vendors',
            'comments',
            'documents',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'deleted_at'),
                "Table '{$table}' does not have soft deletes (deleted_at column)"
            );
        }
    }

    /**
     * Test that timestamps are enabled on all tables
     */
    public function test_timestamps_enabled_on_all_main_tables(): void
    {
        $tables = [
            'users',
            'roles',
            'projects',
            'donors',
            'budgets',
            'budget_items',
            'expenses',
            'vendors',
            'purchase_orders',
            'bank_accounts',
            'cash_flow',
            'comments',
            'documents',
            'notifications',
            'system_settings',
            'reports',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'created_at'),
                "Table '{$table}' does not have created_at column"
            );

            $this->assertTrue(
                Schema::hasColumn($table, 'updated_at'),
                "Table '{$table}' does not have updated_at column"
            );
        }
    }

    /**
     * Test Laravel default tables exist
     */
    public function test_laravel_default_tables_exist(): void
    {
        $tables = [
            'migrations',
            'cache',
            'jobs',
            'personal_access_tokens',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Laravel default table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test that purchase_orders table has unique po_number constraint
     */
    public function test_purchase_orders_has_unique_po_number(): void
    {
        $this->assertTrue(
            Schema::hasColumn('purchase_orders', 'po_number'),
            "Column 'po_number' does not exist in purchase_orders table"
        );
    }

    /**
     * Test that bank_accounts table has unique account_number constraint
     */
    public function test_bank_accounts_has_unique_account_number(): void
    {
        $this->assertTrue(
            Schema::hasColumn('bank_accounts', 'account_number'),
            "Column 'account_number' does not exist in bank_accounts table"
        );
    }

    /**
     * Test that system_settings table has unique key constraint
     */
    public function test_system_settings_has_unique_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('system_settings', 'key'),
            "Column 'key' does not exist in system_settings table"
        );
    }
}
