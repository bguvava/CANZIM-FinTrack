<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        // Create test user
        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $this->financeOfficerRole->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function finance_officer_can_list_vendors()
    {
        Sanctum::actingAs($this->financeOfficer);

        Vendor::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/vendors');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'vendor_code',
                        'name',
                        'contact_person',
                        'email',
                        'phone',
                        'address',
                        'payment_terms',
                        'is_active',
                    ],
                ],
            ])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function finance_officer_can_create_vendor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendorData = [
            'name' => 'ABC Supplies Ltd',
            'contact_person' => 'John Doe',
            'email' => 'john@abcsupplies.com',
            'phone' => '+263771234567',
            'address' => '123 Main Street, Harare',
            'tax_id' => 'TAX12345',
            'payment_terms' => 'Net 30',
        ];

        $response = $this->postJson('/api/v1/vendors', $vendorData);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'vendor' => [
                    'id',
                    'vendor_code',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'name' => 'ABC Supplies Ltd',
            'email' => 'john@abcsupplies.com',
        ]);
    }

    /** @test */
    public function vendor_code_is_auto_generated()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendorData = [
            'name' => 'Test Vendor',
            'contact_person' => 'Jane Doe',
            'email' => 'jane@testvendor.com',
            'phone' => '+263771234567',
            'address' => '456 Test Street',
            'payment_terms' => 'Net 30',
        ];

        $response = $this->postJson('/api/v1/vendors', $vendorData);

        $response->assertCreated();

        $vendor = Vendor::where('email', 'jane@testvendor.com')->first();
        $this->assertNotNull($vendor->vendor_code);
        $this->assertStringStartsWith('VEN-', $vendor->vendor_code);
    }

    /** @test */
    public function finance_officer_can_update_vendor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendor = Vendor::factory()->create([
            'name' => 'Old Name',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'contact_person' => $vendor->contact_person,
            'email' => $vendor->email,
            'phone' => $vendor->phone,
            'address' => $vendor->address,
            'payment_terms' => $vendor->payment_terms,
        ];

        $response = $this->putJson("/api/v1/vendors/{$vendor->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'message' => 'Vendor updated successfully',
            ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function finance_officer_can_deactivate_vendor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendor = Vendor::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/vendors/{$vendor->id}/deactivate");

        $response->assertOk();

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function finance_officer_can_activate_vendor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendor = Vendor::factory()->inactive()->create();

        $response = $this->postJson("/api/v1/vendors/{$vendor->id}/activate");

        $response->assertOk();

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_filter_active_vendors()
    {
        Sanctum::actingAs($this->financeOfficer);

        Vendor::factory()->count(3)->create(['is_active' => true]);
        Vendor::factory()->count(2)->inactive()->create();

        $response = $this->getJson('/api/v1/vendors?is_active=1');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_search_vendors_by_name()
    {
        Sanctum::actingAs($this->financeOfficer);

        Vendor::factory()->create(['name' => 'ABC Suppliers']);
        Vendor::factory()->create(['name' => 'XYZ Trading']);
        Vendor::factory()->create(['name' => 'ABC Logistics']);

        $response = $this->getJson('/api/v1/vendors?search=ABC');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function vendor_name_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendorData = [
            'contact_person' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '+263771234567',
        ];

        $response = $this->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function vendor_email_must_be_valid()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendorData = [
            'name' => 'Test Vendor',
            'contact_person' => 'John Doe',
            'email' => 'invalid-email',
            'phone' => '+263771234567',
        ];

        $response = $this->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function vendor_email_must_be_unique()
    {
        Sanctum::actingAs($this->financeOfficer);

        $existingVendor = Vendor::factory()->create([
            'email' => 'existing@vendor.com',
        ]);

        $vendorData = [
            'name' => 'New Vendor',
            'contact_person' => 'Jane Doe',
            'email' => 'existing@vendor.com',
            'phone' => '+263771234567',
        ];

        $response = $this->postJson('/api/v1/vendors', $vendorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_vendors()
    {
        $response = $this->getJson('/api/v1/vendors');

        $response->assertUnauthorized();
    }
}
