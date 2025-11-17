<?php

namespace Tests\Feature\CashFlow;

use App\Models\BankAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $projectOfficer;

    protected Role $financeOfficerRole;

    protected Role $projectOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        // Create test users
        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);

        $this->projectOfficer = User::create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345680',
            'office_location' => 'Bulawayo Office',
            'role_id' => $this->projectOfficerRole->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function finance_officer_can_list_all_bank_accounts()
    {
        Sanctum::actingAs($this->financeOfficer);

        $accounts = BankAccount::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/bank-accounts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'account_name',
                        'account_number',
                        'bank_name',
                        'branch',
                        'currency',
                        'current_balance',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function finance_officer_can_create_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $accountData = [
            'account_name' => 'CANZIM Main Operating Account',
            'account_number' => '1234567890',
            'bank_name' => 'Standard Bank',
            'branch' => 'Harare Main Branch',
            'currency' => 'USD',
            'current_balance' => 50000.00,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/bank-accounts', $accountData);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'account' => [
                    'id',
                    'account_name',
                    'account_number',
                    'bank_name',
                    'branch',
                    'currency',
                    'current_balance',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'account_name' => 'CANZIM Main Operating Account',
            'account_number' => '1234567890',
            'bank_name' => 'Standard Bank',
        ]);
    }

    /** @test */
    public function finance_officer_can_view_single_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $account = BankAccount::factory()->create();

        $response = $this->getJson("/api/v1/bank-accounts/{$account->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'account_name',
                'account_number',
                'bank_name',
                'branch',
                'currency',
                'current_balance',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $account->id,
                'account_name' => $account->account_name,
            ]);
    }

    /** @test */
    public function finance_officer_can_update_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $account = BankAccount::factory()->create([
            'account_name' => 'Old Account Name',
        ]);

        $updateData = [
            'account_name' => 'Updated Account Name',
            'account_number' => $account->account_number,
            'bank_name' => $account->bank_name,
            'branch' => $account->branch,
            'currency' => $account->currency,
            'current_balance' => $account->current_balance,
            'is_active' => $account->is_active,
        ];

        $response = $this->putJson("/api/v1/bank-accounts/{$account->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'message' => 'Bank account updated successfully',
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'account_name' => 'Updated Account Name',
        ]);
    }

    /** @test */
    public function finance_officer_can_deactivate_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $account = BankAccount::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/bank-accounts/{$account->id}/deactivate");

        $response->assertOk()
            ->assertJson([
                'message' => 'Bank account deactivated successfully',
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function finance_officer_can_activate_bank_account()
    {
        Sanctum::actingAs($this->financeOfficer);

        $account = BankAccount::factory()->inactive()->create();

        $response = $this->postJson("/api/v1/bank-accounts/{$account->id}/activate");

        $response->assertOk()
            ->assertJson([
                'message' => 'Bank account activated successfully',
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_filter_active_bank_accounts()
    {
        Sanctum::actingAs($this->financeOfficer);

        BankAccount::factory()->count(2)->create(['is_active' => true]);
        BankAccount::factory()->count(1)->inactive()->create();

        $response = $this->getJson('/api/v1/bank-accounts?is_active=1');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_inactive_bank_accounts()
    {
        Sanctum::actingAs($this->financeOfficer);

        BankAccount::factory()->count(2)->create(['is_active' => true]);
        BankAccount::factory()->count(1)->inactive()->create();

        $response = $this->getJson('/api/v1/bank-accounts?is_active=0');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function account_name_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $accountData = [
            'account_number' => '1234567890',
            'bank_name' => 'Standard Bank',
            'branch' => 'Harare Main Branch',
            'currency' => 'USD',
            'current_balance' => 50000.00,
        ];

        $response = $this->postJson('/api/v1/bank-accounts', $accountData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_name']);
    }

    /** @test */
    public function account_number_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $accountData = [
            'account_name' => 'CANZIM Main Operating Account',
            'bank_name' => 'Standard Bank',
            'branch' => 'Harare Main Branch',
            'currency' => 'USD',
            'current_balance' => 50000.00,
        ];

        $response = $this->postJson('/api/v1/bank-accounts', $accountData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_number']);
    }

    /** @test */
    public function account_number_must_be_unique()
    {
        Sanctum::actingAs($this->financeOfficer);

        $existingAccount = BankAccount::factory()->create([
            'account_number' => '1234567890',
        ]);

        $accountData = [
            'account_name' => 'CANZIM Main Operating Account',
            'account_number' => '1234567890',
            'bank_name' => 'Standard Bank',
            'branch' => 'Harare Main Branch',
            'currency' => 'USD',
            'current_balance' => 50000.00,
        ];

        $response = $this->postJson('/api/v1/bank-accounts', $accountData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_number']);
    }

    /** @test */
    public function current_balance_must_be_numeric()
    {
        Sanctum::actingAs($this->financeOfficer);

        $accountData = [
            'account_name' => 'CANZIM Main Operating Account',
            'account_number' => '1234567890',
            'bank_name' => 'Standard Bank',
            'branch' => 'Harare Main Branch',
            'currency' => 'USD',
            'current_balance' => 'not-a-number',
        ];

        $response = $this->postJson('/api/v1/bank-accounts', $accountData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_balance']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_bank_accounts()
    {
        $response = $this->getJson('/api/v1/bank-accounts');

        $response->assertUnauthorized();
    }
}
