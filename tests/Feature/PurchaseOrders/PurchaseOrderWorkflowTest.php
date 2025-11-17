<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeOfficer;

    protected User $programsManager;

    protected Role $financeOfficerRole;

    protected Role $programsManagerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->financeOfficerRole = Role::create([
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);

        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
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

        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $this->programsManagerRole->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function finance_officer_can_submit_draft_po_for_approval()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->draft()->create([
            'created_by' => $this->financeOfficer->id,
        ]);

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/submit");

        $response->assertOk()
            ->assertJson([
                'message' => 'Purchase order submitted for approval',
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Pending',
            'submitted_by' => $this->financeOfficer->id,
        ]);
    }

    /** @test */
    public function cannot_submit_non_draft_po()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/submit");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only draft purchase orders can be submitted',
            ]);
    }

    /** @test */
    public function programs_manager_can_approve_pending_po()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->pending()->create([
            'submitted_by' => $this->financeOfficer->id,
        ]);

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/approve");

        $response->assertOk()
            ->assertJson([
                'message' => 'Purchase order approved successfully',
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Approved',
            'approved_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function cannot_approve_non_pending_po()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->draft()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/approve");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only pending purchase orders can be approved',
            ]);
    }

    /** @test */
    public function programs_manager_can_reject_pending_po_with_reason()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/reject", [
            'rejection_reason' => 'Price too high, please negotiate',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Purchase order rejected',
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Rejected',
            'rejected_by' => $this->programsManager->id,
            'rejection_reason' => 'Price too high, please negotiate',
        ]);
    }

    /** @test */
    public function rejection_reason_is_required()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function finance_officer_can_mark_po_items_as_received()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->withoutItems()->approved()->create();
        // Delete any factory-created items
        $po->items()->delete();

        $item = $po->items()->create([
            'line_number' => 1,
            'description' => 'Test Item',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100.00,
            'total_price' => 1000.00,
            'quantity_received' => 0,
        ]);

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/receive", [
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity_received' => 10,
                    'received_date' => now()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Items marked as received',
            ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $item->id,
            'quantity_received' => 10,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Received',
        ]);
    }

    /** @test */
    public function can_partially_receive_items()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->withoutItems()->approved()->create();
        // Delete any factory-created items
        $po->items()->delete();

        $item = $po->items()->create([
            'line_number' => 1,
            'description' => 'Test Item',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 100.00,
            'total_price' => 1000.00,
            'quantity_received' => 0,
        ]);

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/receive", [
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity_received' => 5,
                    'received_date' => now()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $item->id,
            'quantity_received' => 5,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Partially Received',
        ]);
    }

    /** @test */
    public function finance_officer_can_mark_po_as_completed()
    {
        Sanctum::actingAs($this->financeOfficer);

        $po = PurchaseOrder::factory()->received()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/complete");

        $response->assertOk()
            ->assertJson([
                'message' => 'Purchase order marked as completed',
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Completed',
        ]);
    }

    /** @test */
    public function can_cancel_purchase_order_with_reason()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/cancel", [
            'cancellation_reason' => 'Vendor no longer available',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Purchase order cancelled',
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'Cancelled',
        ]);
    }

    /** @test */
    public function cannot_cancel_completed_purchase_order()
    {
        Sanctum::actingAs($this->programsManager);

        $po = PurchaseOrder::factory()->completed()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/cancel", [
            'cancellation_reason' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot cancel completed purchase order',
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_perform_workflow_actions()
    {
        $po = PurchaseOrder::factory()->pending()->create();

        $response = $this->postJson("/api/v1/purchase-orders/{$po->id}/approve");

        $response->assertUnauthorized();
    }
}
