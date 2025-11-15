<?php

declare(strict_types=1);

namespace Tests\Feature\Expenses;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $projectOfficer;

    protected User $financeOfficer;

    protected User $programsManager;

    protected Project $project;

    protected BudgetItem $budgetItem;

    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake notifications to prevent actual sending during tests
        Notification::fake();

        // Seed roles first
        $this->seed(\Database\Seeders\RolesSeeder::class);

        // Get roles
        $projectOfficerRole = Role::where('slug', 'project-officer')->first();
        $financeOfficerRole = Role::where('slug', 'finance-officer')->first();
        $programsManagerRole = Role::where('slug', 'programs-manager')->first();

        // Create test users with proper role_id
        $this->projectOfficer = User::factory()->create([
            'role_id' => $projectOfficerRole->id,
            'status' => 'active',
        ]);
        $this->financeOfficer = User::factory()->create([
            'role_id' => $financeOfficerRole->id,
            'status' => 'active',
        ]);
        $this->programsManager = User::factory()->create([
            'role_id' => $programsManagerRole->id,
            'status' => 'active',
        ]);

        // Create test data
        $this->project = Project::factory()->create(['status' => 'Active']);

        // Create Budget first, then BudgetItem (proper hierarchy)
        $budget = Budget::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->budgetItem = BudgetItem::factory()->create([
            'budget_id' => $budget->id,
            'allocated_amount' => 10000,
            'spent_amount' => 0,
        ]);

        $this->category = ExpenseCategory::factory()->create();

        Storage::fake('public');
    }

    /** @test */
    public function project_officer_can_create_expense(): void
    {
        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'project_id' => $this->project->id,
                'budget_item_id' => $this->budgetItem->id,
                'expense_category_id' => $this->category->id,
                'expense_date' => now()->format('Y-m-d'),
                'amount' => 500,
                'description' => 'Test expense',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'expense']);

        $this->assertDatabaseHas('expenses', [
            'project_id' => $this->project->id,
            'amount' => 500,
            'status' => 'Draft',
        ]);
    }

    /** @test */
    public function project_officer_can_submit_expense(): void
    {
        $expense = Expense::factory()->draft()->create([
            'project_id' => $this->project->id,
            'submitted_by' => $this->projectOfficer->id,
        ]);

        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/submit");

        $response->assertStatus(200);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'Submitted',
        ]);
    }

    /** @test */
    public function finance_officer_can_review_expense(): void
    {
        $expense = Expense::factory()->submitted()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/review", [
                'action' => 'approve',
                'comments' => 'Approved by FO',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'Under Review',
            'reviewed_by' => $this->financeOfficer->id,
        ]);
    }

    /** @test */
    public function programs_manager_can_approve_expense(): void
    {
        $expense = Expense::factory()->underReview()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'amount' => 500,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/approve", [
                'action' => 'approve',
                'comments' => 'Approved by PM',
            ]);

        $response->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Approved', $expense->status);
        $this->assertEquals($this->programsManager->id, $expense->approved_by);

        // Verify budget updated
        $this->budgetItem->refresh();
        $this->assertEquals(500, $this->budgetItem->spent_amount);
    }

    /** @test */
    public function finance_officer_can_mark_as_paid(): void
    {
        $expense = Expense::factory()->approved()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/mark-paid", [
                'payment_reference' => 'PAY-12345',
                'payment_method' => 'Bank Transfer',
                'payment_notes' => 'Paid successfully',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'Paid',
            'payment_reference' => 'PAY-12345',
        ]);
    }

    /** @test */
    public function project_officer_cannot_edit_submitted_expense(): void
    {
        $expense = Expense::factory()->submitted()->create([
            'submitted_by' => $this->projectOfficer->id,
        ]);

        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->putJson("/api/v1/expenses/{$expense->id}", [
                'amount' => 1000,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function project_officer_can_only_view_own_expenses(): void
    {
        $ownExpense = Expense::factory()->create([
            'submitted_by' => $this->projectOfficer->id,
        ]);

        $otherExpense = Expense::factory()->create([
            'submitted_by' => $this->financeOfficer->id,
        ]);

        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->getJson('/api/v1/expenses');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $ownExpense->id])
            ->assertJsonMissing(['id' => $otherExpense->id]);
    }

    /** @test */
    public function expense_can_be_rejected_at_any_stage(): void
    {
        $expense = Expense::factory()->underReview()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/approve", [
                'action' => 'reject',
                'comments' => 'Insufficient documentation',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'Rejected',
            'rejection_reason' => 'Insufficient documentation',
        ]);
    }

    /** @test */
    public function expense_can_include_receipt_upload(): void
    {
        $file = UploadedFile::fake()->create('receipt.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'project_id' => $this->project->id,
                'budget_item_id' => $this->budgetItem->id,
                'expense_category_id' => $this->category->id,
                'expense_date' => now()->format('Y-m-d'),
                'amount' => 500,
                'description' => 'Test expense with receipt',
                'receipt' => $file,
            ]);

        $response->assertStatus(201);

        $expense = Expense::latest()->first();
        $this->assertNotNull($expense->receipt_path);
        $this->assertTrue(Storage::disk('public')->exists($expense->receipt_path));
    }

    /** @test */
    public function complete_approval_workflow(): void
    {
        // 1. Create expense
        $expense = Expense::factory()->draft()->create([
            'project_id' => $this->project->id,
            'budget_item_id' => $this->budgetItem->id,
            'submitted_by' => $this->projectOfficer->id,
            'amount' => 750,
        ]);

        // 2. Submit
        $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/submit")
            ->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Submitted', $expense->status);

        // 3. Finance Officer Review
        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/review", [
                'action' => 'approve',
                'comments' => 'FO Approved',
            ])
            ->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Under Review', $expense->status);

        // 4. Programs Manager Approval
        $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/approve", [
                'action' => 'approve',
                'comments' => 'PM Approved',
            ])
            ->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Approved', $expense->status);

        // 5. Mark as Paid
        $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/expenses/{$expense->id}/mark-paid", [
                'payment_reference' => 'PAY-TEST-001',
                'payment_method' => 'Bank Transfer',
            ])
            ->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Paid', $expense->status);
        $this->assertNotNull($expense->paid_at);
    }
}
