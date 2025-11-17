<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Expense;
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

        // Create expected inflows
        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->addDays(5),
            'amount' => 5000.00,
        ]);

        // Create expected outflows
        Expense::factory()->create([
            'project_id' => $this->project->id,
            'expense_date' => now()->addDays(10),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson('/api/v1/cash-flow/projections');

        $response->assertOk()
            ->assertJsonStructure([
                'current_balance',
                'projected_balance',
                'expected_inflows' => [
                    '*' => ['date', 'amount', 'description'],
                ],
                'expected_outflows' => [
                    '*' => ['date', 'amount', 'description'],
                ],
            ]);

        $data = $response->json();
        $this->assertEquals(10000.00, $data['current_balance']);
        $this->assertEquals(13000.00, $data['projected_balance']); // 10000 + 5000 - 2000
    }

    /** @test */
    public function can_filter_projections_by_date_range()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->addDays(45),
            'amount' => 3000.00,
        ]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->addDays(10),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson('/api/v1/cash-flow/projections?days=30');

        $response->assertOk();

        $inflows = $response->json('expected_inflows');
        $this->assertCount(1, $inflows); // Only the 10-day inflow should be included
    }

    /** @test */
    public function can_view_projections_by_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $bankAccount2 = BankAccount::factory()->create(['current_balance' => 5000.00]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->addDays(5),
            'amount' => 1000.00,
        ]);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $bankAccount2->id,
            'transaction_date' => now()->addDays(5),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson("/api/v1/cash-flow/projections?bank_account_id={$this->bankAccount->id}");

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals(10000.00, $data['current_balance']);
        $this->assertEquals(11000.00, $data['projected_balance']);
    }

    /** @test */
    public function projections_include_warning_for_negative_balance()
    {
        Sanctum::actingAs($this->financeOfficer);

        $this->bankAccount->update(['current_balance' => 1000.00]);

        Expense::factory()->create([
            'project_id' => $this->project->id,
            'expense_date' => now()->addDays(5),
            'amount' => 2000.00,
        ]);

        $response = $this->getJson('/api/v1/cash-flow/projections');

        $response->assertOk()
            ->assertJson([
                'warning' => 'Projected balance will be negative',
            ]);

        $this->assertEquals(-1000.00, $response->json('projected_balance'));
    }

    /** @test */
    public function unauthenticated_user_cannot_view_projections()
    {
        $response = $this->getJson('/api/v1/cash-flow/projections');

        $response->assertUnauthorized();
    }
}
