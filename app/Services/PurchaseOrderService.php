<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * Create a new purchase order.
     */
    public function createPurchaseOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $subtotal * 0.15; // 15% tax
            $totalAmount = $subtotal + $taxAmount;

            // Determine if this should be submitted for approval
            $submitForApproval = isset($data['status']) && strtolower($data['status']) === 'pending';

            // Create PO with Draft status initially
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generatePONumber(),
                'vendor_id' => $data['vendor_id'],
                'project_id' => $data['project_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'Draft',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create PO items
            foreach ($items as $index => $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'line_number' => $index + 1,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'quantity_received' => 0,
                ]);
            }

            // If user requested to submit for approval, do it now
            if ($submitForApproval) {
                $purchaseOrder->update([
                    'status' => 'Pending',
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                ]);
            }

            return $purchaseOrder->load('items');
        });
    }

    /**
     * Update an existing purchase order.
     */
    public function updatePurchaseOrder(PurchaseOrder $purchaseOrder, array $data, ?array $items = null): PurchaseOrder
    {
        if (! $purchaseOrder->canBeEdited()) {
            throw new \Exception('Purchase order cannot be edited in its current status.');
        }

        return DB::transaction(function () use ($purchaseOrder, $data, $items) {
            // Update basic data
            $purchaseOrder->update([
                'vendor_id' => $data['vendor_id'] ?? $purchaseOrder->vendor_id,
                'project_id' => $data['project_id'] ?? $purchaseOrder->project_id,
                'order_date' => $data['order_date'] ?? $purchaseOrder->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $purchaseOrder->expected_delivery_date,
                'notes' => $data['notes'] ?? $purchaseOrder->notes,
                'terms_conditions' => $data['terms_conditions'] ?? $purchaseOrder->terms_conditions,
            ]);

            // Update items if provided
            if ($items !== null) {
                // Delete existing items
                $purchaseOrder->items()->delete();

                // Recalculate totals
                $subtotal = 0;
                foreach ($items as $index => $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $itemTotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'line_number' => $index + 1,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $itemTotal,
                        'quantity_received' => 0,
                    ]);
                }

                $taxAmount = $subtotal * 0.15;
                $totalAmount = $subtotal + $taxAmount;

                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);
            }

            return $purchaseOrder->fresh(['items']);
        });
    }

    /**
     * Submit purchase order for approval.
     */
    public function submitForApproval(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (! $purchaseOrder->canBeSubmitted()) {
            throw new \Exception('Only draft purchase orders can be submitted');
        }

        $purchaseOrder->update([
            'status' => 'Pending',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Approve purchase order.
     */
    public function approvePurchaseOrder(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (! $purchaseOrder->canBeApproved()) {
            throw new \Exception('Only pending purchase orders can be approved');
        }

        $purchaseOrder->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Reject purchase order.
     */
    public function rejectPurchaseOrder(PurchaseOrder $purchaseOrder, string $reason): PurchaseOrder
    {
        if (! $purchaseOrder->canBeRejected()) {
            throw new \Exception('Purchase order cannot be rejected. It must be in Pending status.');
        }

        $purchaseOrder->update([
            'status' => 'Rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Mark items as received.
     */
    public function markItemsReceived(PurchaseOrder $purchaseOrder, array $receivedItems): PurchaseOrder
    {
        if (! $purchaseOrder->canReceiveItems()) {
            throw new \Exception('Items cannot be received for this purchase order.');
        }

        return DB::transaction(function () use ($purchaseOrder, $receivedItems) {
            foreach ($receivedItems as $itemData) {
                $item = PurchaseOrderItem::findOrFail($itemData['item_id']);

                $newQuantityReceived = $item->quantity_received + $itemData['quantity_received'];
                if ($newQuantityReceived > $item->quantity) {
                    throw new \Exception("Cannot receive more than ordered quantity for item: {$item->description}");
                }

                $item->update([
                    'quantity_received' => $newQuantityReceived,
                    'received_date' => $itemData['received_date'] ?? now(),
                ]);
            }

            // Check if all items are fully received
            $allItemsReceived = $purchaseOrder->items()->get()->every(fn ($item) => $item->isFullyReceived());

            if ($allItemsReceived) {
                $purchaseOrder->update([
                    'status' => 'Received',
                    'actual_delivery_date' => now(),
                ]);
            } else {
                $purchaseOrder->update(['status' => 'Partially Received']);
            }

            return $purchaseOrder->fresh(['items']);
        });
    }

    /**
     * Complete purchase order.
     */
    public function completePurchaseOrder(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'Received') {
            throw new \Exception('Purchase order must be fully received before completion.');
        }

        $purchaseOrder->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * Cancel purchase order.
     */
    public function cancelPurchaseOrder(PurchaseOrder $purchaseOrder, string $reason): PurchaseOrder
    {
        if (! in_array($purchaseOrder->status, ['Draft', 'Pending', 'Rejected'])) {
            throw new \Exception('Cannot cancel completed purchase order');
        }

        $purchaseOrder->update([
            'status' => 'Cancelled',
            'rejection_reason' => $reason,
        ]);

        return $purchaseOrder->fresh();
    }
}
