<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashFlowPDFTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

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

        $this->bankAccount = BankAccount::factory()->create();
    }

    /** @test */
    public function finance_officer_can_export_cash_flow_statement_pdf()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->inflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(5),
            'amount' => 5000.00,
        ]);

        CashFlow::factory()->outflow()->create([
            'bank_account_id' => $this->bankAccount->id,
            'transaction_date' => now()->subDays(3),
            'amount' => 2000.00,
        ]);

        $queryParams = http_build_query([
            'bank_account_id' => $this->bankAccount->id,
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson("/api/v1/cash-flow/export-pdf?{$queryParams}");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function can_export_bank_reconciliation_report_pdf()
    {
        Sanctum::actingAs($this->financeOfficer);

        CashFlow::factory()->reconciled()->create([
            'bank_account_id' => $this->bankAccount->id,
            'amount' => 1000.00,
        ]);

        CashFlow::factory()->create([
            'bank_account_id' => $this->bankAccount->id,
            'amount' => 500.00,
            'is_reconciled' => false,
        ]);

        $response = $this->getJson("/api/v1/bank-accounts/{$this->bankAccount->id}/reconciliation-report-pdf");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function date_range_is_required_for_cash_flow_pdf()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->getJson('/api/v1/cash-flow/export-pdf');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function unauthenticated_user_cannot_export_pdf_reports()
    {
        $response = $this->getJson('/api/v1/cash-flow/export-pdf');

        $response->assertUnauthorized();
    }
}
