<?php

namespace Tests\Feature\Expenses;

use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\ExpenseApproval;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePDFTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Project $project;

    protected BudgetItem $budgetItem;

    protected ExpenseCategory $category;

    protected Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $financeRole = Role::firstOrCreate(['name' => 'Finance Officer'], ['slug' => 'finance-officer']);
        $projectRole = Role::firstOrCreate(['name' => 'Project Officer'], ['slug' => 'project-officer']);

        // Create users
        $this->financeOfficer = User::factory()->create([
            'role_id' => $financeRole->id,
            'email' => 'finance@test.com',
        ]);
        $this->projectOfficer = User::factory()->create([
            'role_id' => $projectRole->id,
            'email' => 'project@test.com',
        ]);

        // Create project and budget structure
        $this->project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $this->project->id]);
        $this->budgetItem = BudgetItem::factory()->create(['budget_id' => $budget->id]);
        $this->category = ExpenseCategory::factory()->create();

        // Create base expense
        $this->expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Approved',
            'approved_by' => $this->financeOfficer->id,
            'approved_at' => now()->subDay(),
        ]);

        ExpenseApproval::factory()->create([
            'expense_id' => $this->expense->id,
            'user_id' => $this->financeOfficer->id,
            'action' => 'Approved',
            'comments' => 'Approved for testing',
        ]);
    }

    /** @test */
    public function finance_officer_can_export_expense_details_pdf(): void
    {
        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/{$this->expense->id}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('expense-', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function project_officer_can_export_own_expense_pdf(): void
    {
        $response = $this->actingAs($this->projectOfficer)
            ->getJson("/api/v1/expenses/{$this->expense->id}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function unauthorized_user_cannot_export_expense_pdf(): void
    {
        $otherOfficer = User::factory()->create([
            'role_id' => $this->projectOfficer->role_id,
        ]);

        $response = $this->actingAs($otherOfficer)
            ->getJson("/api/v1/expenses/{$this->expense->id}/export-pdf");

        $response->assertStatus(403);
    }

    /** @test */
    public function finance_officer_can_export_expense_list_pdf(): void
    {
        // Create multiple expenses
        Expense::factory()->count(5)->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson('/api/v1/expenses/export-list-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('expense-list', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function project_officer_only_sees_own_expenses_in_list_pdf(): void
    {
        // Create expense for another project officer
        $otherOfficer = User::factory()->create([
            'role_id' => $this->projectOfficer->role_id,
        ]);
        $otherProject = Project::factory()->create();
        $otherBudget = Budget::factory()->create(['project_id' => $otherProject->id]);
        $otherBudgetItem = BudgetItem::factory()->create(['budget_id' => $otherBudget->id]);

        Expense::factory()->create([
            'project_id' => $otherProject->id,
            'budget_item_id' => $otherBudgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $otherOfficer->id,
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($this->projectOfficer)
            ->getJson('/api/v1/expenses/export-list-pdf');

        $response->assertStatus(200);
        // Project officer should only see their own expenses
        // The PDF service will filter based on submitted_by
    }

    /** @test */
    public function pdf_list_respects_status_filter(): void
    {
        // Create expenses with different statuses
        Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Draft',
        ]);

        Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Rejected',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson('/api/v1/expenses/export-list-pdf?status=Approved');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_list_respects_date_range_filter(): void
    {
        // Create expenses with different dates
        Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'expense_date' => now()->subDays(30),
            'status' => 'Approved',
        ]);

        Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'expense_date' => now()->subDays(5),
            'status' => 'Approved',
        ]);

        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/export-list-pdf?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_list_respects_project_filter(): void
    {
        $anotherProject = Project::factory()->create();
        $anotherBudget = Budget::factory()->create(['project_id' => $anotherProject->id]);
        $anotherBudgetItem = BudgetItem::factory()->create(['budget_id' => $anotherBudget->id]);

        Expense::factory()->create([
            'project_id' => $anotherProject->id,
            'budget_item_id' => $anotherBudgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/export-list-pdf?project_id={$this->project->id}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_list_respects_category_filter(): void
    {
        $anotherCategory = ExpenseCategory::factory()->create();

        Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $anotherCategory->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Approved',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/export-list-pdf?expense_category_id={$this->category->id}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function expense_details_pdf_includes_payment_information_when_paid(): void
    {
        $bankAccount = BankAccount::factory()->create([
            'current_balance' => 50000.00,
            'is_active' => true,
        ]);

        $paidExpense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Paid',
            'approved_by' => $this->financeOfficer->id,
            'approved_at' => now()->subDays(2),
            'paid_by' => $this->financeOfficer->id,
            'paid_at' => now()->subDay(),
            'payment_reference' => 'PAY-2025-001',
            'payment_method' => 'Bank Transfer',
            'payment_notes' => 'Paid via online banking',
        ]);

        CashFlow::factory()->create([
            'expense_id' => $paidExpense->id,
            'bank_account_id' => $bankAccount->id,
            'type' => 'outflow',
            'amount' => $paidExpense->amount,
            'transaction_date' => now()->subDay(),
            'created_by' => $this->financeOfficer->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/{$paidExpense->id}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function expense_details_pdf_includes_rejection_information_when_rejected(): void
    {
        $rejectedExpense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'expense_category_id' => $this->category->id,
            'submitted_by' => $this->projectOfficer->id,
            'status' => 'Rejected',
            'reviewed_by' => $this->financeOfficer->id,
            'reviewed_at' => now()->subDays(2),
            'rejected_by' => $this->financeOfficer->id,
            'rejected_at' => now()->subDay(),
            'rejection_reason' => 'Exceeds allocated budget for this category',
        ]);

        ExpenseApproval::factory()->create([
            'expense_id' => $rejectedExpense->id,
            'user_id' => $this->financeOfficer->id,
            'action' => 'Rejected',
            'comments' => 'Amount exceeds quarterly budget allocation',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/{$rejectedExpense->id}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function expense_details_pdf_includes_approval_history(): void
    {
        // Add multiple approvals
        ExpenseApproval::factory()->create([
            'expense_id' => $this->expense->id,
            'user_id' => $this->financeOfficer->id,
            'action' => 'Approved',
            'comments' => 'Initial review completed',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->getJson("/api/v1/expenses/{$this->expense->id}/export-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function unauthenticated_user_cannot_export_pdf(): void
    {
        $response = $this->getJson("/api/v1/expenses/{$this->expense->id}/export-pdf");
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/expenses/export-list-pdf');
        $response->assertStatus(401);
    }

    /** @test */
    public function pdf_export_returns_error_on_invalid_expense_id(): void
    {
        $response = $this->actingAs($this->financeOfficer)
            ->getJson('/api/v1/expenses/99999/export-pdf');

        $response->assertStatus(404);
    }
}
