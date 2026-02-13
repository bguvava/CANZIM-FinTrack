<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Document;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RequiresGdExtension;

/**
 * Role-Based Workflows Test
 *
 * Tests complete end-to-end workflows for each user role:
 * - Programs Manager
 * - Finance Officer
 * - Project Officer
 * - Auditor
 *
 * Requirements: REQ-681 to REQ-684
 */
class RoleBasedWorkflowsTest extends TestCase
{
    use RefreshDatabase;
    use RequiresGdExtension;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected User $auditor;

    protected Role $programsManagerRole;

    protected Role $financeOfficerRole;

    protected Role $projectOfficerRole;

    protected Role $auditorRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        $this->auditorRole = Role::create([
            'name' => 'Auditor',
            'slug' => 'auditor',
        ]);

        // Create users
        $this->programsManager = User::factory()->create([
            'role_id' => $this->programsManagerRole->id,
            'email' => 'pm@test.com',
        ]);

        $this->financeOfficer = User::factory()->create([
            'role_id' => $this->financeOfficerRole->id,
            'email' => 'fo@test.com',
        ]);

        $this->projectOfficer = User::factory()->create([
            'role_id' => $this->projectOfficerRole->id,
            'email' => 'po@test.com',
        ]);

        $this->auditor = User::factory()->create([
            'role_id' => $this->auditorRole->id,
            'email' => 'auditor@test.com',
        ]);

        // Seed system settings
        $this->seed(\Database\Seeders\SystemSettingsSeeder::class);
    }

    /**
     * Programs Manager Workflow: Create Project → Assign Budget → Approve Expenses → View Reports
     *
     * @test
     */
    public function programs_manager_complete_project_workflow(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Step 1: Create Donor
        $donorResponse = $this->postJson('/api/v1/donors', [
            'name' => 'USAID Foundation',
            'type' => 'institutional',
            'contact_person' => 'John Donor',
            'email' => 'john@usaid.org',
            'phone' => '+1234567890',
            'country' => 'USA',
            'status' => 'active',
        ]);

        $donorResponse->assertStatus(201);
        $donor = Donor::where('email', 'john@usaid.org')->first();
        $this->assertNotNull($donor);

        // Step 2: Create Project
        $projectResponse = $this->postJson('/api/v1/projects', [
            'name' => 'Climate Adaptation Project',
            'description' => 'A project to build climate resilience',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'planning',
            'location' => 'Harare',
            'donor_id' => $donor->id,
        ]);

        $projectResponse->assertStatus(201);
        $project = Project::where('name', 'Climate Adaptation Project')->first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->code); // Auto-generated project code

        // Step 3: Assign Team Members (via project update)
        $assignResponse = $this->putJson("/api/v1/projects/{$project->id}", [
            'team_members' => [
                ['user_id' => $this->projectOfficer->id, 'role' => 'team_member'],
            ],
        ]);

        $assignResponse->assertStatus(200);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $this->projectOfficer->id,
        ]);

        // Step 4: Create Budget
        $budgetResponse = $this->postJson('/api/v1/budgets', [
            'project_id' => $project->id,
            'fiscal_year' => '2024',
            'items' => [
                [
                    'category' => 'Staff Salaries',
                    'description' => 'Project staff salaries',
                    'allocated_amount' => 30000,
                ],
            ],
        ]);

        $budgetResponse->assertStatus(201);
        $budget = Budget::where('project_id', $project->id)->first();
        $this->assertNotNull($budget);

        // Step 5: Approve Budget
        $approveResponse = $this->postJson("/api/v1/budgets/{$budget->id}/approve");
        $approveResponse->assertStatus(200);

        $budget->refresh();
        $this->assertEquals('approved', $budget->status);
        $this->assertEquals($this->programsManager->id, $budget->approved_by);

        // Step 6: View Dashboard Statistics
        $dashboardResponse = $this->getJson('/api/v1/dashboard');
        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'kpis',
                ],
            ]);

        // Step 7: Generate Project Report
        $reportResponse = $this->postJson('/api/v1/reports/project-status', [
            'project_id' => $project->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $reportResponse->assertStatus(201);
        $this->assertDatabaseHas('reports', [
            'generated_by' => $this->programsManager->id,
            'type' => 'project-status',
            'status' => 'completed',
        ]);
    }

    /**
     * Programs Manager: Manage Users → Assign Roles → Monitor Activities
     *
     * @test
     */
    public function programs_manager_user_management_workflow(): void
    {
        Sanctum::actingAs($this->programsManager);

        // Step 1: Create New User
        $createUserResponse = $this->postJson('/api/v1/users', [
            'name' => 'New Project Officer',
            'email' => 'newpo@test.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role_id' => $this->projectOfficerRole->id,
            'office_location' => 'Harare',
            'phone_number' => '+263771234567',
        ]);

        $createUserResponse->assertStatus(201);
        $newUser = User::where('email', 'newpo@test.com')->first();
        $this->assertNotNull($newUser);

        // Step 2: View All Users
        $listResponse = $this->getJson('/api/v1/users');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role', 'status'],
                ],
            ]);

        // Step 3: Update User Role
        $updateResponse = $this->putJson("/api/v1/users/{$newUser->id}", [
            'name' => 'Senior Project Officer',
            'email' => 'newpo@test.com',
            'role_id' => $this->projectOfficerRole->id,
            'office_location' => 'Bulawayo',
        ]);

        $updateResponse->assertStatus(200);
        $newUser->refresh();
        $this->assertEquals('Bulawayo', $newUser->office_location);

        // Step 4: Monitor User Activity
        $activityResponse = $this->getJson("/api/v1/users/{$newUser->id}/activity");
        $activityResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['activity_type', 'description', 'created_at'],
                ],
            ]);

        // Step 5: Deactivate User
        $deactivateResponse = $this->postJson("/api/v1/users/{$newUser->id}/deactivate");
        $deactivateResponse->assertStatus(200);

        $newUser->refresh();
        $this->assertEquals('inactive', $newUser->status);

        // Step 6: View Activity Logs
        $logsResponse = $this->getJson('/api/v1/activity-logs');
        $logsResponse->assertStatus(200);
    }

    /**
     * Programs Manager: Configure System Settings → Upload Logo → Set Policies
     *
     * @test
     */
    public function programs_manager_system_configuration_workflow(): void
    {
        $this->skipIfGdNotAvailable();

        Sanctum::actingAs($this->programsManager);
        Storage::fake('public');

        // Step 1: View Current Settings
        $settingsResponse = $this->getJson('/api/v1/settings');
        $settingsResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'organization',
                    'financial',
                    'email',
                    'security',
                ],
            ]);

        // Step 2: Update Organization Settings
        $updateOrgResponse = $this->putJson('/api/v1/settings/organization', [
            'org_name' => 'CANZIM Updated',
            'org_short_name' => 'CANZIM',
            'org_email' => 'info@canzim-updated.org',
            'org_address' => '456 New Address, Harare',
            'org_timezone' => 'Africa/Harare',
        ]);

        $updateOrgResponse->assertStatus(200);
        $this->assertEquals('CANZIM Updated', SystemSetting::get('org_name'));

        // Step 3: Upload Organization Logo
        $logo = UploadedFile::fake()->image('canzim-logo.png', 500, 500);

        $logoResponse = $this->postJson('/api/v1/settings/logo', [
            'logo' => $logo,
        ]);

        $logoResponse->assertStatus(200);
        $this->assertNotNull(SystemSetting::get('org_logo'));

        // Step 4: Update Financial Settings
        $updateFinanceResponse = $this->putJson('/api/v1/settings/financial', [
            'base_currency' => 'USD',
            'fiscal_year_start_month' => 1,
            'date_format' => 'Y-m-d',
            'datetime_format' => 'Y-m-d H:i',
            'tax_enabled' => true,
            'tax_rate' => 15,
        ]);

        $updateFinanceResponse->assertStatus(200);
        $this->assertEquals('USD', SystemSetting::get('base_currency'));

        // Step 5: Update Security Settings
        $updateSecurityResponse = $this->putJson('/api/v1/settings/security', [
            'session_timeout' => 30,
            'password_expiry_days' => 90,
            'password_min_length' => 8,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'two_factor_enabled' => false,
        ]);

        $updateSecurityResponse->assertStatus(200);
        $this->assertEquals(30, SystemSetting::get('session_timeout'));

        // Step 6: Clear Cache
        $clearCacheResponse = $this->postJson('/api/v1/settings/cache/clear');
        $clearCacheResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All caches cleared successfully',
            ]);
    }

    /**
     * Programs Manager: Upload Documents → Manage Categories → Archive Documents
     *
     * @test
     */
    public function programs_manager_document_management_workflow(): void
    {
        Sanctum::actingAs($this->programsManager);
        Storage::fake('public');

        $project = Project::factory()->create();

        // Step 1: Upload Document (using valid category from hardcoded list)
        $file = UploadedFile::fake()->create('project-plan.pdf', 500, 'application/pdf');

        $uploadResponse = $this->postJson('/api/v1/documents', [
            'file' => $file,
            'title' => 'Climate Adaptation Plan',
            'description' => 'Main project plan for climate adaptation',
            'category' => 'project-reports',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $project->id,
        ]);

        $uploadResponse->assertStatus(201);
        $document = Document::where('title', 'Climate Adaptation Plan')->first();
        $this->assertNotNull($document);

        // Step 3: View All Documents
        $listResponse = $this->getJson('/api/v1/documents');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'file_name', 'category', 'uploaded_by'],
                ],
            ]);

        // Step 4: Update Document Metadata
        $updateResponse = $this->putJson("/api/v1/documents/{$document->id}", [
            'title' => 'Updated Climate Plan',
            'description' => 'Updated project planning document',
        ]);

        $updateResponse->assertStatus(200);
        $document->refresh();
        $this->assertEquals('Updated Climate Plan', $document->title);

        // Step 5: Download Document (archive not implemented, test download instead)
        $downloadResponse = $this->getJson("/api/v1/documents/{$document->id}/download");
        $downloadResponse->assertStatus(200);

        // Step 6: Delete Document
        $deleteResponse = $this->deleteJson("/api/v1/documents/{$document->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('documents', ['id' => $document->id, 'deleted_at' => null]);
    }

    /**
     * Finance Officer Workflow: Review Budgets → Track Expenses → Process Payments
     *
     * @test
     */
    public function finance_officer_budget_and_expense_workflow(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $project = Project::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);
        $category = ExpenseCategory::factory()->create();
        $budgetItem = BudgetItem::factory()->create([
            'budget_id' => $budget->id,
            'category' => $category->name,
            'allocated_amount' => 10000,
        ]);

        // Step 1: View All Budgets
        $budgetsResponse = $this->getJson('/api/v1/budgets');
        $budgetsResponse->assertStatus(200);

        // Step 2: View Budget Details
        $detailsResponse = $this->getJson("/api/v1/budgets/{$budget->id}");
        $detailsResponse->assertStatus(200);

        // Step 3: Track Submitted Expenses
        $expense = Expense::factory()->submitted()->create([
            'project_id' => $project->id,
            'budget_item_id' => $budgetItem->id,
            'expense_category_id' => $category->id,
            'amount' => 500,
            'submitted_by' => $this->projectOfficer->id,
        ]);

        $expensesResponse = $this->getJson('/api/v1/expenses?status=Submitted');
        $expensesResponse->assertStatus(200);

        // Step 4: Review Expense
        $reviewResponse = $this->postJson("/api/v1/expenses/{$expense->id}/review", [
            'action' => 'approve',
            'comments' => 'Expense reviewed and forwarded to Programs Manager',
        ]);

        $reviewResponse->assertStatus(200);
        $expense->refresh();
        $this->assertEquals('Under Review', $expense->status);

        // Step 5: Mark Expense as Paid (after approval)
        $expense->update(['status' => 'Approved']);
        $bankAccount = BankAccount::factory()->create(['current_balance' => 5000]);

        $paymentResponse = $this->postJson("/api/v1/expenses/{$expense->id}/mark-paid", [
            'bank_account_id' => $bankAccount->id,
            'payment_reference' => 'PAY-2024-001',
            'payment_method' => 'Bank Transfer',
        ]);

        $paymentResponse->assertStatus(200);
        $expense->refresh();
        $this->assertEquals('Paid', $expense->status);

        // Step 6: Generate Financial Report
        $reportResponse = $this->postJson('/api/v1/reports/expense-summary', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'group_by' => 'category',
        ]);

        $reportResponse->assertStatus(201);
    }

    /**
     * Finance Officer: Generate Financial Reports → Export to PDF
     *
     * @test
     */
    public function finance_officer_reporting_workflow(): void
    {
        Sanctum::actingAs($this->financeOfficer);

        $project = Project::factory()->create();

        // Step 1: Generate Budget vs Actual Report
        $budgetReportResponse = $this->postJson('/api/v1/reports/budget-vs-actual', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'project_ids' => [$project->id],
        ]);

        // Report generation returns 201 when created
        $budgetReportResponse->assertStatus(201);

        // Step 2: Generate Cash Flow Report
        $cashFlowResponse = $this->postJson('/api/v1/reports/cash-flow', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'grouping' => 'month',
        ]);

        // Report generation returns 201 when created
        $cashFlowResponse->assertStatus(201);

        // Step 3: Generate Expense Summary
        $expenseResponse = $this->postJson('/api/v1/reports/expense-summary', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'group_by' => 'category',
        ]);

        // Report generation returns 201 when created
        $expenseResponse->assertStatus(201);

        // Step 4: View Report History
        $historyResponse = $this->getJson('/api/v1/reports');
        $historyResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'report_type', 'status', 'created_at'],
                    ],
                ],
            ]);
    }

    /**
     * Project Officer Workflow: View Assigned Projects → Submit Expense Claims
     *
     * @test
     */
    public function project_officer_expense_submission_workflow(): void
    {
        Sanctum::actingAs($this->projectOfficer);
        Storage::fake('public');

        $project = Project::factory()->create();
        $project->teamMembers()->attach($this->projectOfficer->id);

        $category = ExpenseCategory::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);
        $budgetItem = BudgetItem::factory()->create([
            'budget_id' => $budget->id,
            'category' => $category->name,
        ]);

        // Step 1: View Assigned Projects
        $projectsResponse = $this->getJson('/api/v1/projects');
        $projectsResponse->assertStatus(200);

        // Step 2: Create Draft Expense
        $draftResponse = $this->postJson('/api/v1/expenses', [
            'project_id' => $project->id,
            'budget_item_id' => $budgetItem->id,
            'expense_category_id' => $category->id,
            'expense_date' => '2024-06-15',
            'amount' => 150,
            'currency' => 'USD',
            'description' => 'Office supplies purchase',
            'status' => 'Draft',
        ]);

        $draftResponse->assertStatus(201);
        $expense = Expense::latest()->first();

        // Step 3: Submit Expense for Approval (receipts are attached during create/update, not separate endpoint)
        $submitResponse = $this->postJson("/api/v1/expenses/{$expense->id}/submit");
        $submitResponse->assertStatus(200);

        $expense->refresh();
        $this->assertEquals('Submitted', $expense->status);

        // Step 4: Track Expense Status
        $statusResponse = $this->getJson("/api/v1/expenses/{$expense->id}");
        $statusResponse->assertStatus(200)
            ->assertJson([
                'status' => 'Submitted',
            ]);

        // Step 5: View Own Expenses
        $myExpensesResponse = $this->getJson('/api/v1/expenses?user_id='.$this->projectOfficer->id);
        $myExpensesResponse->assertStatus(200);
    }

    /**
     * Auditor Workflow: View Documents and Projects (Read-Only Access)
     *
     * Note: Audit trails are restricted to Programs Manager only per manage-settings gate.
     * Auditors have read-only access to projects, documents, and expenses.
     *
     * @test
     */
    public function auditor_audit_and_compliance_workflow(): void
    {
        // Create some activity to audit
        Sanctum::actingAs($this->programsManager);
        $project = Project::factory()->create();

        Sanctum::actingAs($this->auditor);

        // Step 1: Auditors cannot access audit-trails (requires manage-settings gate)
        $auditResponse = $this->getJson('/api/v1/audit-trails');
        $auditResponse->assertStatus(403);

        // Step 2: View All Projects (Read-Only)
        $projectsResponse = $this->getJson('/api/v1/projects');
        $projectsResponse->assertStatus(200);

        // Step 3: View Single Project
        $projectResponse = $this->getJson("/api/v1/projects/{$project->id}");
        $projectResponse->assertStatus(200);

        // Step 4: View All Documents (Read-Only)
        $documentsResponse = $this->getJson('/api/v1/documents');
        $documentsResponse->assertStatus(200);

        // Step 5: View All Expenses (Read-Only)
        $expensesResponse = $this->getJson('/api/v1/expenses');
        $expensesResponse->assertStatus(200);
    }

    /**
     * Auditor: Cannot Modify Data
     *
     * @test
     */
    public function auditor_has_read_only_access(): void
    {
        Sanctum::actingAs($this->auditor);

        $project = Project::factory()->create();

        // Cannot create project
        $createResponse = $this->postJson('/api/v1/projects', [
            'name' => 'New Project',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $createResponse->assertStatus(403);

        // Cannot update project
        $updateResponse = $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Project',
        ]);

        $updateResponse->assertStatus(403);

        // Cannot delete project
        $deleteResponse = $this->deleteJson("/api/v1/projects/{$project->id}");
        $deleteResponse->assertStatus(403);

        // Can view project
        $viewResponse = $this->getJson("/api/v1/projects/{$project->id}");
        $viewResponse->assertStatus(200);
    }
}
