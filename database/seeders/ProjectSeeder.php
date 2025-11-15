<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Donor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for project assignment
        $programsManager = User::whereHas('role', function ($q) {
            $q->where('name', 'Programs Manager');
        })->first();

        $projectOfficers = User::whereHas('role', function ($q) {
            $q->where('name', 'Project Officer');
        })->take(3)->get();

        $donors = Donor::all();

        // Create sample projects
        $projects = [
            [
                'name' => 'Zimbabwe Climate Resilience Initiative',
                'description' => 'Building community resilience to climate change through sustainable agriculture and water management',
                'start_date' => '2024-01-01',
                'end_date' => '2026-12-31',
                'total_budget' => 850000.00,
                'status' => 'active',
                'office_location' => 'Harare',
            ],
            [
                'name' => 'Renewable Energy Advocacy Project',
                'description' => 'Promoting renewable energy adoption and policy advocacy across Zimbabwe',
                'start_date' => '2024-06-01',
                'end_date' => '2025-12-31',
                'total_budget' => 450000.00,
                'status' => 'active',
                'office_location' => 'Bulawayo',
            ],
            [
                'name' => 'Youth Climate Action Network',
                'description' => 'Empowering youth to lead climate action through education and community projects',
                'start_date' => '2025-01-01',
                'end_date' => '2027-06-30',
                'total_budget' => 320000.00,
                'status' => 'planning',
                'office_location' => 'Mutare',
            ],
        ];

        foreach ($projects as $index => $projectData) {
            // Create project
            $project = Project::create(array_merge($projectData, [
                'code' => 'PROJ-2025-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'created_by' => $programsManager->id,
            ]));

            // Assign donors
            if ($donors->count() > 0) {
                $donor = $donors->random();
                $project->donors()->attach($donor->id, [
                    'funding_amount' => $projectData['total_budget'],
                    'funding_period_start' => $projectData['start_date'],
                    'funding_period_end' => $projectData['end_date'],
                    'is_restricted' => false,
                ]);
            }

            // Assign team members
            if ($projectOfficers->count() > 0) {
                foreach ($projectOfficers as $officer) {
                    $project->teamMembers()->attach($officer->id, [
                        'role' => 'team_member',
                    ]);
                }
            }

            // Create budget for active projects
            if ($project->status === 'active') {
                $budget = Budget::create([
                    'project_id' => $project->id,
                    'fiscal_year' => '2025',
                    'total_amount' => $projectData['total_budget'],
                    'status' => 'approved',
                    'approved_by' => $programsManager->id,
                    'approved_at' => now(),
                    'created_by' => $programsManager->id,
                ]);

                // Create budget items
                $categories = [
                    ['category' => 'Staff Salaries', 'percentage' => 0.40],
                    ['category' => 'Travel', 'percentage' => 0.20],
                    ['category' => 'Procurement/Supplies', 'percentage' => 0.15],
                    ['category' => 'Consultants', 'percentage' => 0.15],
                    ['category' => 'Other', 'percentage' => 0.10],
                ];

                foreach ($categories as $cat) {
                    $allocated = $projectData['total_budget'] * $cat['percentage'];
                    $spent = $allocated * (rand(10, 60) / 100); // 10-60% spent

                    BudgetItem::create([
                        'budget_id' => $budget->id,
                        'category' => $cat['category'],
                        'description' => $cat['category'].' for '.$project->name,
                        'cost_code' => 'CC-'.rand(1000, 9999),
                        'allocated_amount' => $allocated,
                        'spent_amount' => $spent,
                        'remaining_amount' => $allocated - $spent,
                    ]);
                }
            }
        }
    }
}
