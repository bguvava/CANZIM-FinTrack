<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashFlowControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Role $financeOfficerRole;

    protected Role $projectOfficerRole;

    protected BankAccount $bankAccount;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        // Create test users
        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);

        $this->projectOfficer = User::create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345680',
            'office_location' => 'Bulawayo Office',
            'role_id' => $this->projectOfficerRole->id,
            'status' => 'active',
        ]);

        // Create test data
        $this->bankAccount = BankAccount::factory()->create([
            'current_balance' => 100000.00,
        ]);

        $this->project = Project::factory()->create();
    }

    /** @test */
    public function finance_officer_can_list_all_cash_flow_transactions()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->count(5)->create([
            'bank_account_id' => $this->bankAccount->id,
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->getJson('/api/v1/cash-flows');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_number',
                        'type',
                        'bank_account_id',
                        'project_id',
                        'transaction_date',
                        'amount',
                        'balance_before',
                        'balance_after',
                        'description',
                        'reference',
                        'is_reconciled',
                    ],
                ],
            ])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function finance_officer_can_record_inflow_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $donor = Donor::factory()->create();

        $inflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'donor_id' => $donor->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 5000.00,
            'description' => 'Grant funding received',
            'reference' => 'REF-2025-001',
        ];

        $response = $this->postJson('/api/v1/cash-flows/inflows', $inflowData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'transaction_number',
                    'type',
                    'amount',
                    'balance_before',
                    'balance_after',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'inflow',
                    'amount' => '5000.00',
                ],
            ]);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'inflow',
            'bank_account_id' => $this->bankAccount->id,
            'donor_id' => $donor->id,
            'amount' => 5000.00,
            'description' => 'Grant funding received',
        ]);

        // Verify bank account balance was updated
        $this->bankAccount->refresh();
        $this->assertEquals(105000.00, $this->bankAccount->current_balance);
    }

    /** @test */
    public function finance_officer_can_record_outflow_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 3000.00,
        ]);

        $outflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'expense_id' => $expense->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 3000.00,
            'description' => 'Office supplies payment',
            'reference' => 'REF-2025-002',
        ];

        $response = $this->postJson('/api/v1/cash-flows/outflows', $outflowData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'transaction_number',
                    'type',
                    'amount',
                    'balance_before',
                    'balance_after',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'outflow',
                    'amount' => '3000.00',
                ],
            ]);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'outflow',
            'bank_account_id' => $this->bankAccount->id,
            'expense_id' => $expense->id,
            'amount' => 3000.00,
            'description' => 'Office supplies payment',
        ]);

        // Verify bank account balance was updated
        $this->bankAccount->refresh();
        $this->assertEquals(97000.00, $this->bankAccount->current_balance);
    }

    /** @test */
    public function finance_officer_can_view_single_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->getJson("/api/v1/cash-flows/{$transaction->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'transaction_number',
                    'type',
                    'bank_account_id',
                    'amount',
                    'description',
                    'bank_account',
                    'project',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                ],
            ]);
    }

    /** @test */
    public function finance_officer_can_update_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'description' => 'Old description',
            'created_by' => $this->financeOfficer->id,
        ]);

        $updateData = [
            'bank_account_id' => $transaction->bank_account_id,
            'project_id' => $transaction->project_id,
            'donor_id' => $transaction->donor_id,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'amount' => $transaction->amount,
            'description' => 'Updated description',
            'reference' => $transaction->reference,
        ];

        $response = $this->putJson("/api/v1/cash-flows/inflows/{$transaction->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Inflow transaction updated successfully',
            ]);

        $this->assertDatabaseHas('cash_flows', [
            'id' => $transaction->id,
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function finance_officer_can_delete_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->deleteJson("/api/v1/cash-flows/{$transaction->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Cash flow transaction deleted successfully',
            ]);

        $this->assertSoftDeleted('cash_flows', [
            'id' => $transaction->id,
        ]);
    }

    /** @test */
    public function can_filter_transactions_by_type()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->inflow()->count(3)->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);
        CashFlow::factory()->outflow()->count(2)->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);

        $response = $this->getJson('/api/v1/cash-flows?type=inflow');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_transactions_by_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $otherAccount = BankAccount::factory()->create();

        CashFlow::factory()->count(3)->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);
        CashFlow::factory()->count(2)->create([
            'bank_account_id' => $otherAccount->id,
        ]);

        $response = $this->getJson("/api/v1/cash-flows?bank_account_id={$this->bankAccount->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_transactions_by_date_range()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(10),
        ]);
        CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(5),
        ]);
        CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now(),
        ]);

        $dateFrom = now()->subDays(7)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->getJson("/api/v1/cash-flows?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_reconciled_transactions()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->reconciled()->count(2)->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);
        CashFlow::factory()->count(3)->create([
            'bank_account_id' => $this->bankAccount->id,
            'is_reconciled' => false,
        ]);

        $response = $this->getJson('/api/v1/cash-flows?is_reconciled=1');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function bank_account_id_is_required_for_inflow()
    {
        Sanctum::actingAs($this->financeOfficer);

        $inflowData = [
            'project_id' => $this->project->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 5000.00,
            'description' => 'Grant funding received',
        ];

        $response = $this->postJson('/api/v1/cash-flows/inflows', $inflowData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bank_account_id']);
    }

    /** @test */
    public function amount_is_required_and_must_be_positive()
    {
        Sanctum::actingAs($this->financeOfficer);

        $inflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => -5000.00,
            'description' => 'Grant funding received',
        ];

        $response = $this->postJson('/api/v1/cash-flows/inflows', $inflowData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function donor_id_is_required_for_inflow()
    {
        Sanctum::actingAs($this->financeOfficer);

        $inflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 5000.00,
            'description' => 'Grant funding received',
        ];

        $response = $this->postJson('/api/v1/cash-flows/inflows', $inflowData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['donor_id']);
    }

    /** @test */
    public function expense_id_is_required_for_outflow()
    {
        Sanctum::actingAs($this->financeOfficer);

        $outflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 3000.00,
            'description' => 'Office supplies payment',
        ];

        $response = $this->postJson('/api/v1/cash-flows/outflows', $outflowData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expense_id']);
    }

    /** @test */
    public function cannot_create_outflow_exceeding_bank_balance()
    {
        Sanctum::actingAs($this->financeOfficer);

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 200000.00,
        ]);

        $outflowData = [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'expense_id' => $expense->id,
            'transaction_date' => now()->format('Y-m-d'),
            'amount' => 200000.00,
            'description' => 'Large payment',
        ];

        $response = $this->postJson('/api/v1/cash-flows/outflows', $outflowData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Insufficient bank account balance',
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_cash_flows()
    {
        $response = $this->getJson('/api/v1/cash-flows');

        $response->assertUnauthorized();
    }
}
