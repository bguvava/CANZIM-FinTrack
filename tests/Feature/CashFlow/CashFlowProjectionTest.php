<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashFlowProjectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected Project $project;

    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
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

        $this->project = Project::factory()->create();
        $this->bankAccount = BankAccount::factory()->create(['current_balance' => 10000.00]);
    }

    /** @test */
    public function finance_officer_can_view_cash_flow_projections_for_next_30_days()
    {
        Sanctum::actingAs($this->financeOfficer);

        // Create historical inflows (within last 6 months)
        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(30),
            'amount' => 5000.00,
        ]);

        // Create historical outflows
        CashFlow::factory()->outflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(20),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson("/api/v1/cash-flow/projections?bank_account_id={$this->bankAccount->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'current_balance',
                'avg_monthly_inflow',
                'avg_monthly_outflow',
                'avg_net_cash_flow',
                'months',
                'best_case',
                'likely_case',
                'worst_case',
                'projections',
            ]);

        $data = $response->json();
        $this->assertEquals(10000.00, $data['current_balance']);
        $this->assertGreaterThanOrEqual(0, $data['avg_monthly_inflow']);
    }

    /** @test */
    public function can_filter_projections_by_months()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(30),
            'amount' => 3000.00,
        ]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(10),
            'amount' => 2000.00,
        ]);

        // Request 6 months of projections
        $response = $this->getJson("/api/v1/cash-flow/projections?bank_account_id={$this->bankAccount->id}&months=6");

        $response->assertOk();

        $projections = $response->json('projections');
        $this->assertCount(6, $projections);
    }

    /** @test */
    public function can_view_projections_by_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $bankAccount2 = BankAccount::factory()->create(['current_balance' => 5000.00]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(5),
            'amount' => 1000.00,
        ]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $bankAccount2->id,
            'transaction_date' => now()->subDays(5),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson("/api/v1/cash-flow/projections?bank_account_id={$this->bankAccount->id}");

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals(10000.00, $data['current_balance']);
        // Monthly avg should be inflow amount / 6 months
        $this->assertIsNumeric($data['avg_monthly_inflow']);
    }

    /** @test */
    public function projections_calculate_based_on_historical_data()
    {
        Sanctum::actingAs($this->financeOfficer);

        $this->bankAccount->update(['current_balance' => 1000.00]);

        // Create historical outflows that exceed inflows
        CashFlow::factory()->outflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(30),
            'amount' => 2000.00,
        ]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(20),
            'amount' => 500.00,
        ]);

        $response = $this->getJson("/api/v1/cash-flow/projections?bank_account_id={$this->bankAccount->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'current_balance',
                'avg_monthly_inflow',
                'avg_monthly_outflow',
                'avg_net_cash_flow',
            ]);

        $data = $response->json();
        $this->assertEquals(1000.00, $data['current_balance']);
        // Net cash flow should be negative since outflows > inflows
        $this->assertLessThan(0, $data['avg_net_cash_flow']);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_projections()
    {
        $response = $this->getJson('/api/v1/cash-flow/projections');

        $response->assertUnauthorized();
    }
}
