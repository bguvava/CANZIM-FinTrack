<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        // Create test user
        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);

        // Create bank account
        $this->bankAccount = BankAccount::factory()->create();
    }

    /** @test */
    public function finance_officer_can_reconcile_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'is_reconciled' => false,
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->postJson("/api/v1/cash-flows/{$transaction->id}/reconcile", [
            'reconciliation_date' => now()->format('Y-m-d'),
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('cash_flows', [
            'id' => $transaction->id,
            'is_reconciled' => true,
            'reconciled_by' => $this->financeOfficer->id,
        ]);
    }

    /** @test */
    public function finance_officer_can_unreconcile_transaction()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->reconciled()->create([
            'bank_account_id' => $this->bankAccount->id,
            'reconciled_by' => $this->financeOfficer->id,
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->postJson("/api/v1/cash-flows/{$transaction->id}/unreconcile");

        $response->assertOk();

        $this->assertDatabaseHas('cash_flows', [
            'id' => $transaction->id,
            'is_reconciled' => false,
            'reconciled_by' => null,
            'reconciled_at' => null,
        ]);
    }

    /** @test */
    public function can_filter_unreconciled_transactions()
    {
        Sanctum::actingAs($this->financeOfficer);

        // Create reconciled and unreconciled transactions
        CashFlow::factory()->reconciled()->count(2)->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);
        CashFlow::factory()->count(3)->create([
            'bank_account_id' => $this->bankAccount->id,
            'is_reconciled' => false,
        ]);

        $response = $this->getJson('/api/v1/cash-flows?is_reconciled=0');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
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
    public function reconciliation_date_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $transaction = CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'is_reconciled' => false,
        ]);

        $response = $this->postJson("/api/v1/cash-flows/{$transaction->id}/reconcile", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reconciliation_date']);
    }

    /** @test */
    public function unauthenticated_user_cannot_reconcile()
    {
        $transaction = CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
        ]);

        $response = $this->postJson("/api/v1/cash-flows/{$transaction->id}/reconcile", [
            'reconciliation_date' => now()->format('Y-m-d'),
        ]);

        $response->assertUnauthorized();
    }
}
