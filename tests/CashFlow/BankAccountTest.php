<?php

namespace Tests\CashFlow;

use App\Models\BankAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $programsManager;

    protected function setUp(): void
    {
        parent::setUp();

        $financeRole = Role::factory()->create(['slug' => 'finance-officer']);
        $this->financeOfficer = User::factory()->create(['role_id' => $financeRole->id]);

        $programsRole = Role::factory()->create(['slug' => 'programs-manager']);
        $this->programsManager = User::factory()->create(['role_id' => $programsRole->id]);
    }

    public function test_finance_officer_can_create_bank_account(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/bank-accounts', [
                'account_name' => 'Operations Account',
                'account_number' => '1234567890',
                'bank_name' => 'Standard Bank',
                'branch' => 'Main Branch',
                'currency' => 'ZAR',
                'current_balance' => 50000.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'account' => [
                    'id',
                    'account_name',
                    'account_number',
                    'bank_name',
                    'current_balance',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'account_name' => 'Operations Account',
            'account_number' => '1234567890',
            'is_active' => true,
        ]);
    }

    public function test_finance_officer_can_view_bank_accounts(): void
    {
        BankAccount::factory()->count(3)->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/bank-accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'account_name',
                        'account_number',
                        'bank_name',
                        'current_balance',
                    ],
                ],
            ]);
    }

    public function test_can_filter_bank_accounts_by_active_status(): void
    {
        BankAccount::factory()->create(['is_active' => true]);
        BankAccount::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/bank-accounts?is_active=1');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data')[0]['is_active']);
    }

    public function test_finance_officer_can_deactivate_account(): void
    {
        $account = BankAccount::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/bank-accounts/{$account->id}/deactivate");

        $response->assertStatus(200);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function test_finance_officer_can_view_account_summary(): void
    {
        $account = BankAccount::factory()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson("/api/v1/bank-accounts/{$account->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'account',
                'total_inflows',
                'total_outflows',
                'recent_transactions',
            ]);
    }

    public function test_duplicate_account_number_is_rejected(): void
    {
        BankAccount::factory()->create(['account_number' => '1234567890']);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/bank-accounts', [
                'account_name' => 'New Account',
                'account_number' => '1234567890',
                'bank_name' => 'FNB',
                'currency' => 'ZAR',
                'current_balance' => 10000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_number']);
    }
}
