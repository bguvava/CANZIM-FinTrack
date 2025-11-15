<?php

namespace Tests\PurchaseOrders;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $programsManager;

    protected Vendor $vendor;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $financeRole = Role::factory()->create(['slug' => 'finance-officer']);
        $this->financeOfficer = User::factory()->create(['role_id' => $financeRole->id]);

        $programsRole = Role::factory()->create(['slug' => 'programs-manager']);
        $this->programsManager = User::factory()->create(['role_id' => $programsRole->id]);

        $this->vendor = Vendor::factory()->create();
        $this->project = Project::factory()->create();
    }

    public function test_finance_officer_can_create_purchase_order(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/purchase-orders', [
                'vendor_id' => $this->vendor->id,
                'project_id' => $this->project->id,
                'order_date' => now()->format('Y-m-d'),
                'expected_delivery_date' => now()->addDays(30)->format('Y-m-d'),
                'notes' => 'Test purchase order',
                'items' => [
                    [
                        'description' => 'Office Desk',
                        'quantity' => 5,
                        'unit' => 'pcs',
                        'unit_price' => 2500.00,
                    ],
                    [
                        'description' => 'Office Chair',
                        'quantity' => 5,
                        'unit' => 'pcs',
                        'unit_price' => 1500.00,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'purchase_order' => [
                    'id',
                    'po_number',
                    'status',
                    'subtotal',
                    'tax_amount',
                    'total_amount',
                    'items',
                ],
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'status' => 'Draft',
        ]);

        // Verify items created
        $poId = $response->json('purchase_order.id');
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $poId,
            'description' => 'Office Desk',
            'quantity' => 5,
        ]);
    }

    public function test_po_number_is_auto_generated(): void
    {
        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/purchase-orders', [
                'vendor_id' => $this->vendor->id,
                'project_id' => $this->project->id,
                'order_date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'description' => 'Test Item',
                        'quantity' => 1,
                        'unit' => 'pcs',
                        'unit_price' => 100,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $poNumber = $response->json('purchase_order.po_number');

        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{4}$/', $poNumber);
    }

    public function test_finance_officer_can_submit_po_for_approval(): void
    {
        $po = PurchaseOrder::factory()->create([
            'status' => 'Draft',
            'created_by' => $this->financeOfficer->id,
        ]);
        $po->items()->create([
            'line_number' => 1,
            'description' => 'Test item',
            'quantity' => 1,
            'unit' => 'pcs',
            'unit_price' => 100,
            'total_price' => 100,
            'quantity_received' => 0,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/purchase-orders/{$po->id}/submit");

        $response->assertStatus(200);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Pending',
            'submitted_by' => $this->financeOfficer->id,
        ]);
    }

    public function test_programs_manager_can_approve_po(): void
    {
        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/purchase-orders/{$po->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Approved',
            'approved_by' => $this->programsManager->id,
        ]);
    }

    public function test_programs_manager_can_reject_po(): void
    {
        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson("/api/v1/purchase-orders/{$po->id}/reject", [
                'rejection_reason' => 'Budget constraints',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Rejected',
            'rejection_reason' => 'Budget constraints',
        ]);
    }

    public function test_finance_officer_can_mark_items_as_received(): void
    {
        $po = PurchaseOrder::factory()->approved()->create();
        $item = $po->items()->create([
            'line_number' => 1,
            'description' => 'Test item',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100,
            'total_price' => 1000,
            'quantity_received' => 0,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/purchase-orders/{$po->id}/receive", [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'quantity_received' => 10,
                        'received_date' => now()->format('Y-m-d'),
                    ],
                ],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $item->id,
            'quantity_received' => 10,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Received',
        ]);
    }

    public function test_cannot_receive_more_than_ordered_quantity(): void
    {
        $po = PurchaseOrder::factory()->approved()->create();
        $item = $po->items()->create([
            'line_number' => 1,
            'description' => 'Test item',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 100,
            'total_price' => 500,
            'quantity_received' => 0,
        ]);

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson("/api/v1/purchase-orders/{$po->id}/receive", [
                'items' => [
                    [
                        'item_id' => $item->id,
                        'quantity_received' => 10,
                        'received_date' => now()->format('Y-m-d'),
                    ],
                ],
            ]);

        $response->assertStatus(500)
            ->assertJsonFragment(['error' => 'Cannot receive more than ordered quantity for item: Test item']);
    }

    public function test_po_statistics_endpoint_works(): void
    {
        PurchaseOrder::factory()->draft()->count(2)->create();
        PurchaseOrder::factory()->pending()->count(3)->create();
        PurchaseOrder::factory()->approved()->count(1)->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/purchase-orders/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_count',
                'draft_count',
                'pending_count',
                'approved_count',
                'completed_count',
                'total_value',
                'recent_orders',
            ]);
    }

    public function test_can_filter_purchase_orders_by_status(): void
    {
        PurchaseOrder::factory()->draft()->create();
        PurchaseOrder::factory()->pending()->create();

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->getJson('/api/v1/purchase-orders?status=Pending');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn ($item) => $item['status'] === 'Pending'));
    }
}
