<?php

namespace Tests\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected BankAccount $bankAccount;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $financeRole = Role::factory()->create(['slug' => 'finance-officer']);
        $this->financeOfficer = User::factory()->create(['role_id' => $financeRole->id]);

        $this->bankAccount = BankAccount::factory()->create(['current_balance' => 100000]);
        $this->project = Project::factory()->create();
    }

    public function test_finance_officer_can_record_inflow(): void
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/cash-flows/inflow', [
                'bank_account_id' => $this->bankAccount->id,
                'project_id' => $this->project->id,
                'donor_id' => $donor->id,
                'transaction_date' => now()->format('Y-m-d'),
                'amount' => 25000.00,
                'description' => 'Donor contribution',
                'reference' => 'DON-2025-001',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'cash_flow' => [
                    'id',
                    'transaction_number',
                    'type',
                    'amount',
                    'balance_before',
                    'balance_after',
                ],
            ]);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'inflow',
            'amount' => 25000.00,
            'bank_account_id' => $this->bankAccount->id,
        ]);

        // Verify bank balance updated
        $this->assertEquals(125000, $this->bankAccount->fresh()->current_balance);
    }

    public function test_finance_officer_can_record_outflow(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/cash-flows/outflow', [
                'bank_account_id' => $this->bankAccount->id,
                'project_id' => $this->project->id,
                'transaction_date' => now()->format('Y-m-d'),
                'amount' => 15000.00,
                'description' => 'Office supplies purchase',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'outflow',
            'amount' => 15000.00,
        ]);

        // Verify bank balance updated
        $this->assertEquals(85000, $this->bankAccount->fresh()->current_balance);
    }

    public function test_outflow_rejected_when_insufficient_balance(): void
    {
        $account = BankAccount::factory()->create(['current_balance' => 1000]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/cash-flows/outflow', [
                'bank_account_id' => $account->id,
                'project_id' => $this->project->id,
                'transaction_date' => now()->format('Y-m-d'),
                'amount' => 5000.00,
                'description' => 'Payment',
            ]);

        $response->assertStatus(500)
            ->assertJsonFragment(['error' => 'Insufficient balance in bank account.']);
    }

    public function test_finance_officer_can_reconcile_transaction(): void
    {
        $cashFlow = CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'is_reconciled' => false,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/cash-flows/{$cashFlow->id}/reconcile");

        $response->assertStatus(200);

        $this->assertDatabaseHas('cash_flows', [
            'id' => $cashFlow->id,
            'is_reconciled' => true,
        ]);
    }

    public function test_can_view_cash_flow_projections(): void
    {
        // Create historical data
        CashFlow::factory()->inflow()->count(5)->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subMonths(2),
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson("/api/v1/cash-flows/projections?bank_account_id={$this->bankAccount->id}&months=3");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_balance',
                'avg_monthly_inflow',
                'avg_monthly_outflow',
                'avg_net_cash_flow',
                'projections' => [
                    '*' => [
                        'month',
                        'projected_balance',
                        'projected_inflow',
                        'projected_outflow',
                    ],
                ],
            ]);
    }

    public function test_can_filter_cash_flows_by_type(): void
    {
        CashFlow::factory()->inflow()->create(['bank_account_id' => $this->bankAccount->id]);
        CashFlow::factory()->outflow()->create(['bank_account_id' => $this->bankAccount->id]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/cash-flows?type=inflow');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn ($item) => $item['type'] === 'inflow'));
    }

    public function test_can_view_cash_flow_statistics(): void
    {
        CashFlow::factory()->inflow()->count(3)->create(['bank_account_id' => $this->bankAccount->id]);
        CashFlow::factory()->outflow()->count(2)->create(['bank_account_id' => $this->bankAccount->id]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson("/api/v1/cash-flows/statistics?bank_account_id={$this->bankAccount->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_inflows',
                'total_outflows',
                'reconciled_count',
                'unreconciled_count',
                'recent_transactions',
            ]);
    }

    public function test_transaction_number_is_auto_generated(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/cash-flows/inflow', [
                'bank_account_id' => $this->bankAccount->id,
                'project_id' => $this->project->id,
                'transaction_date' => now()->format('Y-m-d'),
                'amount' => 1000,
                'description' => 'Test',
            ]);

        $response->assertStatus(201);
        $transactionNumber = $response->json('cash_flow.transaction_number');

        $this->assertMatchesRegularExpression('/^TXN-\d{4}-\d{4}$/', $transactionNumber);
    }
}
