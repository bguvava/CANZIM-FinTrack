<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseOrderPDFTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected Vendor $vendor;

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

        $this->vendor = Vendor::factory()->create();
    }

    /** @test */
    public function finance_officer_can_export_single_purchase_order_pdf()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->approved()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $po->items()->create([
            'line_number' => 1,
            'description' => 'Test Item',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100.00,
            'total_price' => 1000.00,
        ]);

        $response = $this->getJson("/api/v1/purchase-orders/{$po->id}/export-pdf");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function can_export_purchase_orders_list_pdf()
    {
        Sanctum::actingAs($this->financeOfficer);

        PurchaseOrder::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->getJson('/api/v1/purchase-orders/export-list-pdf', [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function can_filter_purchase_orders_pdf_by_status()
    {
        Sanctum::actingAs($this->financeOfficer);

        PurchaseOrder::factory()->approved()->create(['vendor_id' => $this->vendor->id]);
        PurchaseOrder::factory()->pending()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->getJson('/api/v1/purchase-orders/export-list-pdf', [
            'status' => 'Approved',
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function unauthenticated_user_cannot_export_purchase_order_pdf()
    {
        $po = PurchaseOrder::factory()->create();

        $response = $this->getJson("/api/v1/purchase-orders/{$po->id}/export-pdf");

        $response->assertUnauthorized();
    }
}
