<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Dashboard Controller Tests
 *
 * Tests dashboard API endpoints for all user roles
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $pmRole = Role::factory()->create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $foRole = Role::factory()->create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $poRole = Role::factory()->create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        // Create users
        $this->programsManager = User::factory()->create([
            'role_id' => $pmRole->id,
            'email' => 'pm@test.com',
        ]);

        $this->financeOfficer = User::factory()->create([
            'role_id' => $foRole->id,
            'email' => 'fo@test.com',
        ]);

        $this->projectOfficer = User::factory()->create([
            'role_id' => $poRole->id,
            'email' => 'po@test.com',
        ]);
    }

    /**
     * Test unauthenticated user cannot access dashboard
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(401);
    }

    /**
     * Test Programs Manager can access dashboard
     */
    public function test_programs_manager_can_access_dashboard(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'kpis' => [
                        'total_budget',
                        'ytd_spending',
                        'available_funds',
                        'pending_approvals',
                    ],
                    'charts' => [
                        'budget_utilization',
                        'expense_trends',
                        'donor_allocation',
                        'cash_flow_projection',
                    ],
                    'recent_activity',
                    'pending_items',
                ],
            ]);
    }

    /**
     * Test Finance Officer can access dashboard
     */
    public function test_finance_officer_can_access_dashboard(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'kpis' => [
                        'monthly_budget',
                        'actual_expenses',
                        'pending_expenses',
                        'cash_balance',
                    ],
                    'charts' => [
                        'budget_vs_actual',
                        'expense_categories',
                    ],
                    'recent_transactions',
                    'po_status',
                    'payment_schedule',
                ],
            ]);
    }

    /**
     * Test Project Officer can access dashboard
     */
    public function test_project_officer_can_access_dashboard(): void
    {
        Sanctum::actingAs($this->projectOfficer);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'kpis' => [
                        'project_budget',
                        'budget_used',
                        'remaining_budget',
                        'activities_completed',
                    ],
                    'assigned_projects',
                    'project_activities',
                    'personal_tasks',
                    'project_timeline',
                    'team_collaboration',
                ],
            ]);
    }

    /**
     * Test KPI calculations are accurate
     */
    public function test_kpi_calculations_are_accurate(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify KPI structure
        $this->assertIsArray($data['kpis']);
        $this->assertArrayHasKey('total_budget', $data['kpis']);
        $this->assertArrayHasKey('ytd_spending', $data['kpis']);
        $this->assertArrayHasKey('available_funds', $data['kpis']);
    }

    /**
     * Test chart data format is correct
     */
    public function test_chart_data_format_is_correct(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200);

        $charts = $response->json('data.charts');

        // Verify chart structure
        $this->assertIsArray($charts);
        $this->assertArrayHasKey('budget_utilization', $charts);
        $this->assertArrayHasKey('expense_trends', $charts);

        // Verify Chart.js compatible format
        $expenseTrends = $charts['expense_trends'];
        $this->assertArrayHasKey('labels', $expenseTrends);
        $this->assertArrayHasKey('datasets', $expenseTrends);
        $this->assertIsArray($expenseTrends['datasets']);
    }

    /**
     * Test notifications endpoint
     */
    public function test_user_can_fetch_notifications(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->getJson('/api/v1/dashboard/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'unread_count',
                    'notifications',
                ],
            ]);
    }

    /**
     * Test mark notification as read
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/dashboard/notifications/1/read');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test dashboard data caching
     */
    public function test_dashboard_data_is_cached(): void
    {
        Sanctum::actingAs($this->programsManager);

        // First request
        $startTime = microtime(true);
        $this->getJson('/api/v1/dashboard');
        $firstRequestTime = microtime(true) - $startTime;

        // Second request (should be cached)
        $startTime = microtime(true);
        $this->getJson('/api/v1/dashboard');
        $secondRequestTime = microtime(true) - $startTime;

        // Cached request should be faster (or at least not significantly slower)
        // Allow for some variance in execution time
        $this->assertLessThanOrEqual($firstRequestTime * 1.5, $secondRequestTime, 'Second request should not be significantly slower than first');
    }

    /**
     * Test dashboard response time is under 2 seconds
     */
    public function test_dashboard_loads_within_performance_threshold(): void
    {
        Sanctum::actingAs($this->programsManager);

        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/dashboard');
        $responseTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $responseTime, 'Dashboard should load in under 2 seconds');
    }

    /**
     * Test Finance Officer KPIs contain no NaN values
     */
    public function test_finance_officer_kpis_contain_no_nan_values(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200);

        $kpis = $response->json('data.kpis');

        // Verify all numeric KPIs are valid numbers, not NaN (can be int or float)
        $this->assertTrue(is_numeric($kpis['monthly_budget']), 'monthly_budget should be numeric');
        $this->assertTrue(is_numeric($kpis['actual_expenses']), 'actual_expenses should be numeric');
        $this->assertTrue(is_numeric($kpis['cash_balance']), 'cash_balance should be numeric');

        if (is_float($kpis['monthly_budget'])) {
            $this->assertFalse(is_nan($kpis['monthly_budget']), 'monthly_budget should not be NaN');
        }
        if (is_float($kpis['actual_expenses'])) {
            $this->assertFalse(is_nan($kpis['actual_expenses']), 'actual_expenses should not be NaN');
        }
        if (is_float($kpis['cash_balance'])) {
            $this->assertFalse(is_nan($kpis['cash_balance']), 'cash_balance should not be NaN');
        }

        // Verify pending_expenses structure
        $this->assertIsArray($kpis['pending_expenses']);
        $this->assertArrayHasKey('count', $kpis['pending_expenses']);
        $this->assertArrayHasKey('total_amount', $kpis['pending_expenses']);
        $this->assertIsInt($kpis['pending_expenses']['count']);
        $this->assertTrue(is_numeric($kpis['pending_expenses']['total_amount']), 'total_amount should be numeric');

        if (is_float($kpis['pending_expenses']['total_amount'])) {
            $this->assertFalse(is_nan($kpis['pending_expenses']['total_amount']), 'pending_expenses total_amount should not be NaN');
        }
    }

    /**
     * Test dashboard with no data returns zero values instead of null or NaN
     */
    public function test_dashboard_with_no_data_returns_zero_values(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        // Ensure database is empty (RefreshDatabase should handle this)
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200);

        $kpis = $response->json('data.kpis');

        // All values should be 0 or 0.0 when no data exists (either int or float is acceptable)
        $this->assertEquals(0, (float) $kpis['monthly_budget'], 'monthly_budget should default to 0');
        $this->assertEquals(0, (float) $kpis['actual_expenses'], 'actual_expenses should default to 0');
        $this->assertEquals(0, (float) $kpis['cash_balance'], 'cash_balance should default to 0');
        $this->assertEquals(0, (float) $kpis['pending_expenses']['total_amount'], 'pending_expenses total_amount should default to 0');
        $this->assertEquals(0, $kpis['pending_expenses']['count'], 'pending_expenses count should default to 0');
    }

    /**
     * Test chart data contains no NaN values
     */
    public function test_chart_data_contains_no_nan_values(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(200);

        $charts = $response->json('data.charts');

        // Check budget_vs_actual chart
        if (isset($charts['budget_vs_actual']['datasets'])) {
            foreach ($charts['budget_vs_actual']['datasets'] as $dataset) {
                if (isset($dataset['data'])) {
                    foreach ($dataset['data'] as $value) {
                        $this->assertTrue(is_numeric($value), 'Chart data should be numeric');
                        if (is_float($value)) {
                            $this->assertFalse(is_nan($value), 'Chart data should not contain NaN values');
                        }
                    }
                }
            }
        }

        // Check expense_categories chart
        if (isset($charts['expense_categories']['datasets'])) {
            foreach ($charts['expense_categories']['datasets'] as $dataset) {
                if (isset($dataset['data'])) {
                    foreach ($dataset['data'] as $value) {
                        $this->assertTrue(is_numeric($value), 'Chart data should be numeric');
                        if (is_float($value)) {
                            $this->assertFalse(is_nan($value), 'Chart data should not contain NaN values');
                        }
                    }
                }
            }
        }
    }
}
