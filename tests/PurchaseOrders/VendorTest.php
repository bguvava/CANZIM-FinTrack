<?php

namespace Tests\PurchaseOrders;

use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $financeRole = Role::factory()->create(['slug' => 'finance-officer']);
        $this->financeOfficer = User::factory()->create(['role_id' => $financeRole->id]);
    }

    public function test_can_create_vendor(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/vendors', [
                'name' => 'ABC Supplies Ltd',
                'contact_person' => 'John Doe',
                'email' => 'contact@abcsupplies.com',
                'phone' => '+27123456789',
                'address' => '123 Main Street, Johannesburg',
                'tax_id' => '1234567890',
                'payment_terms' => 'Net 30',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'vendor' => [
                    'id',
                    'vendor_code',
                    'name',
                    'email',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('vendors', [
            'name' => 'ABC Supplies Ltd',
            'email' => 'contact@abcsupplies.com',
            'is_active' => true,
        ]);
    }

    public function test_vendor_code_is_auto_generated(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/vendors', [
                'name' => 'Test Vendor',
                'email' => 'test@vendor.com',
            ]);

        $response->assertStatus(201);
        $vendorCode = $response->json('vendor.vendor_code');

        $this->assertMatchesRegularExpression('/^VEN-\d{4}-\d{4}$/', $vendorCode);
    }

    public function test_can_view_vendors_list(): void
    {
        Vendor::factory()->count(5)->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'vendor_code',
                        'name',
                        'email',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_can_filter_vendors_by_active_status(): void
    {
        Vendor::factory()->create(['is_active' => true]);
        Vendor::factory()->inactive()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/vendors?is_active=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn ($item) => $item['is_active'] === true));
    }

    public function test_can_deactivate_vendor(): void
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/vendors/{$vendor->id}/deactivate");

        $response->assertStatus(200);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'is_active' => false,
        ]);
    }

    public function test_can_update_vendor_details(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->putJson("/api/v1/vendors/{$vendor->id}", [
                'name' => 'Updated Vendor Name',
                'payment_terms' => 'Net 60',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => 'Updated Vendor Name',
            'payment_terms' => 'Net 60',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Vendor::factory()->create(['email' => 'duplicate@vendor.com']);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/vendors', [
                'name' => 'Another Vendor',
                'email' => 'duplicate@vendor.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_view_vendor_summary(): void
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson("/api/v1/vendors/{$vendor->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'vendor',
                'total_orders',
                'total_value',
                'pending_orders',
                'completed_orders',
                'recent_orders',
            ]);
    }
}
