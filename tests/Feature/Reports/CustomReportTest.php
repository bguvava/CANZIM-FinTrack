<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $programsManagerRole = Role::factory()->create(['name' => 'Programs Manager']);
        $financeOfficerRole = Role::factory()->create(['name' => 'Finance Officer']);
        $regularRole = Role::factory()->create(['name' => 'Program Officer']);

        // Create users
        $this->programsManager = User::factory()->create(['role_id' => $programsManagerRole->id]);
        $this->financeOfficer = User::factory()->create(['role_id' => $financeOfficerRole->id]);
        $this->regularUser = User::factory()->create(['role_id' => $regularRole->id]);
    }

    public function test_programs_manager_can_generate_custom_report(): void
    {
        $project = Project::factory()->create();
        $category = ExpenseCategory::factory()->create();
        Expense::factory()->count(5)->create([
            'project_id' => $project->id,
            'expense_category_id' => $category->id,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
                'filters' => [
                    'project_id' => $project->id,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'entity',
                    'data',
                    'aggregates',
                    'filters_applied',
                    'generated_at',
                ],
            ]);
    }

    public function test_finance_officer_can_generate_custom_report(): void
    {
        $project = Project::factory()->create();
        Expense::factory()->count(3)->create([
            'project_id' => $project->id,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->financeOfficer)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_custom_report_validates_entity(): void
    {
        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'invalid_entity',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['entity']);
    }

    public function test_custom_report_applies_filters_correctly(): void
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        Expense::factory()->count(3)->create(['project_id' => $project1->id, 'status' => 'Paid']);
        Expense::factory()->count(2)->create(['project_id' => $project2->id, 'status' => 'Paid']);

        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
                'filters' => [
                    'project_id' => $project1->id,
                ],
            ]);

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
    }

    public function test_custom_report_supports_grouping(): void
    {
        $project = Project::factory()->create();
        Expense::factory()->count(5)->create([
            'project_id' => $project->id,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
                'grouping' => 'status',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.grouping', 'status');
    }

    public function test_custom_report_can_export_pdf(): void
    {
        $project = Project::factory()->create();
        Expense::factory()->count(3)->create([
            'project_id' => $project->id,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/export', [
                'entity' => 'expenses',
                'filters' => [
                    'project_id' => $project->id,
                ],
            ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unauthorized_user_cannot_generate_custom_report(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
            ]);

        $response->assertStatus(403);
    }

    public function test_custom_report_enforces_max_five_filters(): void
    {
        $response = $this->actingAs($this->programsManager)
            ->postJson('/api/v1/reports/custom/generate', [
                'entity' => 'expenses',
                'filters' => [
                    'filter1' => 'value1',
                    'filter2' => 'value2',
                    'filter3' => 'value3',
                    'filter4' => 'value4',
                    'filter5' => 'value5',
                    'filter6' => 'value6',
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_custom_report_supports_all_entities(): void
    {
        $entities = ['expenses', 'projects', 'budgets', 'donors', 'purchase_orders'];

        foreach ($entities as $entity) {
            $response = $this->actingAs($this->programsManager)
                ->postJson('/api/v1/reports/custom/generate', [
                    'entity' => $entity,
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.entity', $entity);
        }
    }
}
