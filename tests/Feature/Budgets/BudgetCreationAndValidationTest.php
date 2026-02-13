<?php

namespace Tests\Feature\Budgets;

use App\Models\Budget;
use App\Models\Donor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCreationAndValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Project $project;

    protected Donor $donor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->project = Project::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $this->donor = Donor::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_budget_creation_succeeds_with_valid_donor_funding(): void
    {
        // Assign donor to project with $50,000 funding
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 50000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'Staff Salaries',
                    'description' => 'Project staff salaries',
                    'cost_code' => 'SAL-001',
                    'allocated_amount' => 30000.00,
                ],
                [
                    'category' => 'Procurement/Supplies',
                    'description' => 'Office supplies',
                    'cost_code' => 'SUP-001',
                    'allocated_amount' => 10000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'project_id',
                    'fiscal_year',
                    'total_amount',
                    'status',
                    'items',
                ],
            ]);

        $this->assertDatabaseHas('budgets', [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'total_amount' => 40000.00,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('budget_items', 2);
    }

    public function test_budget_creation_fails_when_exceeding_total_donor_funding(): void
    {
        // Assign donor to project with only $30,000 funding
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 30000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        // Try to create a budget for $50,000 (exceeds available funding)
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'Staff Salaries',
                    'description' => 'Project staff salaries',
                    'allocated_amount' => 50000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => 'Budget total ($50,000.00) exceeds available donor funding. Total funding: $30,000.00, Already allocated: $0.00, Available: $30,000.00',
            ]);

        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_budget_creation_fails_when_exceeding_available_funding_with_existing_budgets(): void
    {
        // Assign donor to project with $100,000 funding
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 100000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        // Create first budget for $60,000
        Budget::factory()->create([
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'total_amount' => 60000.00,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        // Try to create second budget for $50,000 (only $40,000 available)
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->addYear()->year,
            'items' => [
                [
                    'category' => 'Travel',
                    'description' => 'Travel expenses',
                    'allocated_amount' => 50000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => 'Budget total ($50,000.00) exceeds available donor funding. Total funding: $100,000.00, Already allocated: $60,000.00, Available: $40,000.00',
            ]);

        // Only the first budget should exist
        $this->assertDatabaseCount('budgets', 1);
    }

    public function test_budget_creation_succeeds_with_remaining_available_funding(): void
    {
        // Assign donor to project with $100,000 funding
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 100000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        // Create first budget for $60,000
        Budget::factory()->create([
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'total_amount' => 60000.00,
            'status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        // Create second budget for exactly $40,000 (all remaining funding)
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->addYear()->year,
            'items' => [
                [
                    'category' => 'Travel',
                    'description' => 'Travel expenses',
                    'allocated_amount' => 40000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseCount('budgets', 2);
    }

    public function test_budget_creation_succeeds_with_multiple_donors(): void
    {
        // Create second donor
        $donor2 = Donor::factory()->create([
            'status' => 'active',
        ]);

        // Assign both donors to project
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 30000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $this->project->donors()->attach($donor2->id, [
            'funding_amount' => 40000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        // Create budget within total funding ($70,000)
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'Staff Salaries',
                    'description' => 'Salaries',
                    'allocated_amount' => 65000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('budgets', [
            'project_id' => $this->project->id,
            'total_amount' => 65000.00,
        ]);
    }

    public function test_budget_creation_succeeds_when_no_donor_funding_exists(): void
    {
        // Project has no donors assigned
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'Other',
                    'description' => 'General expenses',
                    'allocated_amount' => 50000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        // Should succeed - projects without donor funding can still create budgets
        $response->assertStatus(201);

        $this->assertDatabaseHas('budgets', [
            'project_id' => $this->project->id,
            'total_amount' => 50000.00,
        ]);
    }

    public function test_budget_validation_requires_valid_category(): void
    {
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'InvalidCategory',
                    'description' => 'Test',
                    'allocated_amount' => 1000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.category']);
    }

    public function test_budget_validation_requires_at_least_one_item(): void
    {
        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_budget_items_calculate_totals_correctly(): void
    {
        $this->project->donors()->attach($this->donor->id, [
            'funding_amount' => 100000.00,
            'funding_period_start' => now()->toDateString(),
            'funding_period_end' => now()->addYear()->toDateString(),
            'is_restricted' => false,
        ]);

        $budgetData = [
            'project_id' => $this->project->id,
            'fiscal_year' => (string) now()->year,
            'items' => [
                [
                    'category' => 'Staff Salaries',
                    'allocated_amount' => 25000.50,
                ],
                [
                    'category' => 'Travel',
                    'allocated_amount' => 15000.75,
                ],
                [
                    'category' => 'Procurement/Supplies',
                    'allocated_amount' => 10000.25,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201);

        $budget = Budget::latest()->first();

        $this->assertEquals(50001.50, $budget->total_amount);
        $this->assertEquals(3, $budget->items()->count());
    }
}
