<?php

declare(strict_types=1);

namespace Tests\Feature\Expenses;

use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePaymentTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Project $project;

    protected BankAccount $bankAccount;

    protected Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(\Database\Seeders\RolesSeeder::class);

        // Get finance officer role
        $financeOfficerRole = Role::where('slug', 'finance-officer')->first();

        // Create finance officer user
        $this->financeOfficer = User::factory()->create([
            'role_id' => $financeOfficerRole->id,
            'status' => 'active',
        ]);

        // Create project
        $this->project = Project::factory()->create(['status' => 'Active']);

        // Create bank account
        $this->bankAccount = BankAccount::factory()->create([
            'account_name' => 'Test Bank Account',
            'current_balance' => 10000.00,
            'is_active' => true,
        ]);

        // Create budget and budget item
        $budget = Budget::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $budgetItem = BudgetItem::factory()->create([
            'budget_id' => $budget->id,
            'allocated_amount' => 10000,
        ]);

        // Create approved expense
        $this->expense = Expense::factory()->approved()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $budgetItem->id,
            'amount' => 500.00,
        ]);
    }

    /** @test */
    public function mark_as_paid_requires_bank_account_id(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
                'payment_notes' => 'Test payment',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bank_account_id']);
    }

    /** @test */
    public function mark_as_paid_creates_cash_flow_outflow_record(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
                'payment_notes' => 'Test payment',
            ]);

        $response->assertStatus(200);

        // Verify cash flow record created
        $this->assertDatabaseHas('cash_flows', [
            'bank_account_id' => $this->bankAccount->id,
            'project_id' => $this->project->id,
            'expense_id' => $this->expense->id,
            'type' => 'outflow',
            'amount' => 500.00,
        ]);
    }

    /** @test */
    public function mark_as_paid_updates_bank_account_balance(): void
    {
        $initialBalance = $this->bankAccount->current_balance;

        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        // Verify bank account balance updated
        $this->bankAccount->refresh();
        $expectedBalance = $initialBalance - $this->expense->amount;
        $this->assertEquals($expectedBalance, $this->bankAccount->current_balance);
    }

    /** @test */
    public function mark_as_paid_fails_with_insufficient_balance(): void
    {
        // Create bank account with insufficient balance
        $lowBalanceAccount = BankAccount::factory()->create([
            'current_balance' => 100.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $lowBalanceAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ]);

        $response->assertStatus(422);

        // Verify no cash flow record created
        $this->assertDatabaseMissing('cash_flows', [
            'expense_id' => $this->expense->id,
        ]);

        // Verify expense status unchanged
        $this->expense->refresh();
        $this->assertEquals('Approved', $this->expense->status);
    }

    /** @test */
    public function cash_flow_record_links_to_expense(): void
    {
        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        // Get the cash flow record
        $cashFlow = CashFlow::where('expense_id', $this->expense->id)->first();

        $this->assertNotNull($cashFlow);
        $this->assertEquals($this->expense->id, $cashFlow->expense_id);
        $this->assertEquals($this->project->id, $cashFlow->project_id);
        $this->assertEquals($this->bankAccount->id, $cashFlow->bank_account_id);
    }

    /** @test */
    public function cash_flow_includes_payment_reference(): void
    {
        $paymentReference = 'PAY-TEST-001';

        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => $paymentReference,
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        // Verify cash flow has payment reference
        $this->assertDatabaseHas('cash_flows', [
            'expense_id' => $this->expense->id,
            'reference' => $paymentReference,
        ]);
    }

    /** @test */
    public function cash_flow_description_includes_expense_details(): void
    {
        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        // Get cash flow record
        $cashFlow = CashFlow::where('expense_id', $this->expense->id)->first();

        $this->assertNotNull($cashFlow);
        $this->assertStringContainsString($this->expense->expense_number, $cashFlow->description);
        $this->assertStringContainsString('Payment for expense', $cashFlow->description);
    }

    /** @test */
    public function expense_relationship_loads_cash_flow(): void
    {
        // Mark as paid
        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        // Load expense with cash flow relationship
        $expense = Expense::with('cashFlow.bankAccount')->find($this->expense->id);

        $this->assertNotNull($expense->cashFlow);
        $this->assertEquals($this->bankAccount->id, $expense->cashFlow->bank_account_id);
        $this->assertEquals($this->bankAccount->account_name, $expense->cashFlow->bankAccount->account_name);
    }

    /** @test */
    public function bank_account_must_be_active(): void
    {
        // Deactivate bank account
        $this->bankAccount->update(['is_active' => false]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$this->expense->id}/mark-paid", [
                'bank_account_id' => $this->bankAccount->id,
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
            ]);

        $response->assertStatus(422);
    }
}
