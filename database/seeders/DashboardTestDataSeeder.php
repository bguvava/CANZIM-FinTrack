<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CashFlow;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dashboard Test Data Seeder
 *
 * Populates the database with realistic test data for dashboard testing
 * Creates projects, budgets, expenses, cash flows, and activity logs
 */
class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding dashboard test data...');

        // Get or create roles
        $pmRole = Role::firstOrCreate(
            ['slug' => 'programs-manager'],
            ['name' => 'Programs Manager', 'description' => 'Oversees all programs and projects']
        );

        $foRole = Role::firstOrCreate(
            ['slug' => 'finance-officer'],
            ['name' => 'Finance Officer', 'description' => 'Manages financial operations']
        );

        $poRole = Role::firstOrCreate(
            ['slug' => 'project-officer'],
            ['name' => 'Project Officer', 'description' => 'Manages individual projects']
        );

        // Get existing users
        $pm = User::where('role_id', $pmRole->id)->first();
        $fo = User::where('role_id', $foRole->id)->first();
        $po = User::where('role_id', $poRole->id)->first();

        if (! $pm || ! $fo || ! $po) {
            $this->command->warn('Required users not found. Please run UserSeeder first.');

            return;
        }

        // Create expense categories
        $categories = [
            ['name' => 'Office Supplies', 'code' => 'OFFICE', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Travel & Transport', 'code' => 'TRAVEL', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Training & Development', 'code' => 'TRAINING', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Equipment', 'code' => 'EQUIPMENT', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Utilities', 'code' => 'UTILITIES', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        // Create donors
        $donors = [
            ['name' => 'USAID', 'status' => 'active'],
            ['name' => 'World Bank', 'status' => 'active'],
            ['name' => 'EU Development Fund', 'status' => 'active'],
        ];

        foreach ($donors as $donorData) {
            Donor::firstOrCreate(['name' => $donorData['name']], $donorData);
        }

        // Create bank accounts
        $bankAccounts = [
            ['account_name' => 'Operating Account', 'account_number' => 'OPS-001', 'bank_name' => 'National Bank', 'current_balance' => 150000, 'is_active' => true],
            ['account_name' => 'Project Account', 'account_number' => 'PRJ-001', 'bank_name' => 'Commercial Bank', 'current_balance' => 75000, 'is_active' => true],
        ];

        foreach ($bankAccounts as $account) {
            BankAccount::firstOrCreate(
                ['account_number' => $account['account_number']],
                $account
            );
        }

        // Create projects
        $projects = [
            [
                'name' => 'Community Health Program',
                'code' => 'CHP-2026',
                'description' => 'Improving community health services',
                'total_budget' => 250000,
                'status' => 'active',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(6),
                'created_by' => $pm->id,
            ],
            [
                'name' => 'Education Initiative',
                'code' => 'EDU-2026',
                'description' => 'Enhancing educational infrastructure',
                'total_budget' => 180000,
                'status' => 'active',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'created_by' => $pm->id,
            ],
        ];

        $createdProjects = [];
        foreach ($projects as $projectData) {
            $project = Project::firstOrCreate(
                ['code' => $projectData['code']],
                $projectData
            );
            $createdProjects[] = $project;
        }

        // Create budgets for projects
        foreach ($createdProjects as $project) {
            $budget = Budget::firstOrCreate(
                ['project_id' => $project->id, 'fiscal_year' => now()->year],
                [
                    'budget_name' => $project->name.' Budget '.now()->year,
                    'total_amount' => $project->total_budget,
                    'status' => 'approved',
                    'created_by' => $pm->id,
                ]
            );

            // Create budget items
            $expenseCategories = ExpenseCategory::where('is_active', true)->get();
            foreach ($expenseCategories->take(3) as $index => $category) {
                BudgetItem::firstOrCreate(
                    ['budget_id' => $budget->id, 'category' => $category->name],
                    [
                        'description' => $category->name.' allocation',
                        'allocated_amount' => ($project->total_budget / 3),
                        'spent_amount' => ($project->total_budget / 3) * (0.3 + ($index * 0.1)),
                    ]
                );
            }
        }

        // Create expenses for current month
        $expenseCategories = ExpenseCategory::where('is_active', true)->get();
        $expenseStatuses = ['pending', 'approved', 'paid'];

        for ($i = 0; $i < 15; $i++) {
            $category = $expenseCategories->random();
            $project = $createdProjects[array_rand($createdProjects)];
            $status = $expenseStatuses[array_rand($expenseStatuses)];

            Expense::create([
                'expense_number' => 'EXP-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'title' => 'Test Expense '.$i,
                'description' => 'Test expense for dashboard data',
                'amount' => rand(500, 5000),
                'expense_date' => now()->subDays(rand(0, 30)),
                'status' => $status,
                'project_id' => $project->id,
                'expense_category_id' => $category->id,
                'submitted_by' => $po->id,
                'reviewed_by' => $status !== 'pending' ? $fo->id : null,
                'approved_by' => $status === 'paid' ? $pm->id : null,
            ]);
        }

        // Create cash flows
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths(11 - $i);

            // Inflow
            CashFlow::create([
                'reference_number' => 'CF-IN-'.$date->format('Ym').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'type' => 'inflow',
                'category' => 'Grant Received',
                'amount' => rand(15000, 25000),
                'transaction_date' => $date,
                'description' => 'Monthly grant disbursement',
                'status' => 'completed',
            ]);

            // Outflow
            CashFlow::create([
                'reference_number' => 'CF-OUT-'.$date->format('Ym').'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'type' => 'outflow',
                'category' => 'Operating Expenses',
                'amount' => rand(10000, 18000),
                'transaction_date' => $date,
                'description' => 'Monthly operating expenses',
                'status' => 'completed',
            ]);
        }

        // Create purchase orders
        for ($i = 0; $i < 5; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-'.now()->year.'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'vendor_name' => 'Vendor '.($i + 1),
                'total_amount' => rand(2000, 10000),
                'status' => ['pending', 'approved', 'completed'][rand(0, 2)],
                'order_date' => now()->subDays(rand(1, 30)),
                'created_by' => $po->id,
            ]);
        }

        // Create activity logs
        $activityTypes = ['expense_created', 'expense_approved', 'expense_paid', 'project_created', 'budget_approved'];
        for ($i = 0; $i < 20; $i++) {
            ActivityLog::create([
                'user_id' => [$pm->id, $fo->id, $po->id][rand(0, 2)],
                'activity_type' => $activityTypes[array_rand($activityTypes)],
                'description' => 'Test activity log entry '.$i,
                'properties' => json_encode(['test' => true]),
                'created_at' => now()->subHours(rand(1, 168)),
            ]);
        }

        $this->command->info('Dashboard test data seeded successfully!');
        $this->command->info('- Created '.count($categories).' expense categories');
        $this->command->info('- Created '.count($donors).' donors');
        $this->command->info('- Created '.count($bankAccounts).' bank accounts');
        $this->command->info('- Created '.count($createdProjects).' projects');
        $this->command->info('- Created 15 expenses');
        $this->command->info('- Created 24 cash flows');
        $this->command->info('- Created 5 purchase orders');
        $this->command->info('- Created 20 activity logs');
    }
}
