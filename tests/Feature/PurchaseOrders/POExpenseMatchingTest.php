<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class POExpenseMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected Project $project;

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
    }

    /** @test */
    public function can_link_expense_to_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->approved()->create([
            'project_id' => $this->project->id,
        ]);

        $bankAccount = BankAccount::factory()->create();

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 1000.00,
        ]);

        $cashFlow = CashFlow::factory()->outflow()->create([
            'bank_account_id' => $bankAccount->id,
            'expense_id' => $expense->id,
            'amount' => 1000.00,
        ]);

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/link-po", [
            'purchase_order_id' => $po->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Expense linked to purchase order',
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'purchase_order_id' => $po->id,
        ]);
    }

    /** @test */
    public function can_unlink_expense_from_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->approved()->create();

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'purchase_order_id' => $po->id,
        ]);

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/unlink-po");

        $response->assertOk()
            ->assertJson([
                'message' => 'Expense unlinked from purchase order',
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'purchase_order_id' => null,
        ]);
    }

    /** @test */
    public function can_view_expenses_linked_to_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->approved()->create([
            'project_id' => $this->project->id,
        ]);

        $expense1 = Expense::factory()->create([
            'project_id' => $this->project->id,
            'purchase_order_id' => $po->id,
            'amount' => 500.00,
        ]);

        $expense2 = Expense::factory()->create([
            'project_id' => $this->project->id,
            'purchase_order_id' => $po->id,
            'amount' => 300.00,
        ]);

        $response = $this->getJson("/api/v1/purchase-orders/{$po->id}/expenses");

        $response->assertOk()
            ->assertJsonStructure([
                'expenses' => [
                    '*' => ['id', 'amount', 'description', 'expense_date'],
                ],
                'total_expenses',
            ])
            ->assertJson([
                'total_expenses' => 800.00,
            ]);

        $this->assertCount(2, $response->json('expenses'));
    }

    /** @test */
    public function cannot_link_expense_to_draft_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->draft()->create();

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/link-po", [
            'purchase_order_id' => $po->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Can only link expenses to approved or completed purchase orders',
            ]);
    }

    /** @test */
    public function expense_and_po_must_belong_to_same_project()
    {
        Sanctum::actingAs($this->financeOfficer);

        $project2 = Project::factory()->create();

        $po = PurchaseOrder::factory()->approved()->create([
            'project_id' => $this->project->id,
        ]);

        $expense = Expense::factory()->create([
            'project_id' => $project2->id,
        ]);

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/link-po", [
            'purchase_order_id' => $po->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Expense and purchase order must belong to the same project',
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_link_expenses()
    {
        $po = PurchaseOrder::factory()->approved()->create();
        $expense = Expense::factory()->create();

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/link-po", [
            'purchase_order_id' => $po->id,
        ]);

        $response->assertUnauthorized();
    }
}
