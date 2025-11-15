<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected Role $financeOfficerRole;

    protected Vendor $vendor;

    protected Project $project;

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

        // Create vendor and project
        $this->vendor = Vendor::factory()->create();
        $this->project = Project::factory()->create();
    }

    /** @test */
    public function finance_officer_can_list_purchase_orders()
    {
        Sanctum::actingAs($this->financeOfficer);

        PurchaseOrder::factory()->count(3)->create([
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->getJson('/api/v1/purchase-orders');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'po_number',
                        'vendor_id',
                        'project_id',
                        'order_date',
                        'status',
                        'total_amount',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function finance_officer_can_create_purchase_order_with_line_items()
    {
        Sanctum::actingAs($this->financeOfficer);

        $poData = [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Test PO',
            'items' => [
                [
                    'description' => 'Item 1',
                    'specifications' => 'Specs 1',
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'unit_price' => 100.00,
                ],
                [
                    'description' => 'Item 2',
                    'specifications' => 'Specs 2',
                    'quantity' => 5,
                    'unit' => 'boxes',
                    'unit_price' => 200.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/purchase-orders', $poData);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'purchase_order' => [
                    'id',
                    'po_number',
                    'status',
                    'total_amount',
                ],
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'status' => 'Draft',
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'description' => 'Item 1',
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'description' => 'Item 2',
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function po_number_is_auto_generated()
    {
        Sanctum::actingAs($this->financeOfficer);

        $poData = [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit' => 'pcs',
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/purchase-orders', $poData);

        $response->assertCreated();

        $po = PurchaseOrder::latest()->first();
        $this->assertNotNull($po->po_number);
        $this->assertStringStartsWith('PO-', $po->po_number);
    }

    /** @test */
    public function can_update_draft_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->draft()->create([
            'vendor_id' => $this->vendor->id,
            'created_by' => $this->financeOfficer->id,
        ]);

        $updateData = [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'order_date' => $po->order_date->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(45)->format('Y-m-d'),
            'notes' => 'Updated notes',
            'items' => [
                [
                    'description' => 'Updated Item',
                    'quantity' => 5,
                    'unit' => 'pcs',
                    'unit_price' => 150.00,
                ],
            ],
        ];

        $response = $this->putJson("/api/v1/purchase-orders/{$po->id}", $updateData);

        $response->assertOk();

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'notes' => 'Updated notes',
        ]);
    }

    /** @test */
    public function cannot_update_submitted_purchase_order()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->pending()->create([
            'created_by' => $this->financeOfficer->id,
        ]);

        $updateData = [
            'vendor_id' => $po->vendor_id,
            'project_id' => $po->project_id,
            'order_date' => $po->order_date->format('Y-m-d'),
            'expected_delivery_date' => $po->expected_delivery_date->format('Y-m-d'),
            'notes' => 'Updated notes',
            'items' => [],
        ];

        $response = $this->putJson("/api/v1/purchase-orders/{$po->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot update purchase order that is not in draft status',
            ]);
    }

    /** @test */
    public function can_filter_purchase_orders_by_status()
    {
        Sanctum::actingAs($this->financeOfficer);

        PurchaseOrder::factory()->draft()->count(2)->create();
        PurchaseOrder::factory()->pending()->count(3)->create();

        $response = $this->getJson('/api/v1/purchase-orders?status=Draft');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_purchase_orders_by_vendor()
    {
        Sanctum::actingAs($this->financeOfficer);

        $vendor1 = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();

        PurchaseOrder::factory()->count(3)->create(['vendor_id' => $vendor1->id]);
        PurchaseOrder::factory()->count(2)->create(['vendor_id' => $vendor2->id]);

        $response = $this->getJson("/api/v1/purchase-orders?vendor_id={$vendor1->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_search_purchase_orders_by_po_number()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po1 = PurchaseOrder::factory()->create(['po_number' => 'PO-2025-0001']);
        $po2 = PurchaseOrder::factory()->create(['po_number' => 'PO-2025-0002']);
        $po3 = PurchaseOrder::factory()->create(['po_number' => 'PO-2024-0001']);

        $response = $this->getJson('/api/v1/purchase-orders?search=2025');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function vendor_id_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $poData = [
            'project_id' => $this->project->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [],
        ];

        $response = $this->postJson('/api/v1/purchase-orders', $poData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_id']);
    }

    /** @test */
    public function items_array_is_required()
    {
        Sanctum::actingAs($this->financeOfficer);

        $poData = [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'order_date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/purchase-orders', $poData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function items_must_have_required_fields()
    {
        Sanctum::actingAs($this->financeOfficer);

        $poData = [
            'vendor_id' => $this->vendor->id,
            'project_id' => $this->project->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item',
                    // Missing quantity, unit, unit_price
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/purchase-orders', $poData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity', 'items.0.unit_price']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_purchase_orders()
    {
        $response = $this->getJson('/api/v1/purchase-orders');

        $response->assertUnauthorized();
    }
}
