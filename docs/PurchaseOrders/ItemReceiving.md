# Item Receiving Process

## Overview

Item Receiving is the process of recording delivery of goods and services against approved purchase orders. This critical step updates inventory, triggers payment processing, and completes the procurement cycle.

## Receiving Workflow

### Standard Receiving Process

```
1. Goods Delivered
   ↓
2. Verify Against PO
   ↓
3. Inspect Items
   ↓
4. Record Receipt (Partial or Full)
   ↓
5. Update PO Status
   ↓
6. Generate Expense Record (if applicable)
   ↓
7. Process for Payment
```

## Receiving Types

### Full Receiving

**All items delivered in one shipment**

**Process:**

1. Verify all items match PO
2. Inspect quality and quantity
3. Record receipt for all items
4. PO automatically moves to "Received" status
5. Ready for completion

**Example:**

```
PO-2025-0001
Item 1: 10 boxes ordered → 10 boxes received ✓
Item 2: 5 boxes ordered → 5 boxes received ✓
Status: Approved → Received
```

### Partial Receiving

**Items delivered in multiple shipments**

**Process:**

1. Receive first shipment
2. Record quantities received
3. PO remains in "Approved" status
4. Later shipments arrive
5. Record additional quantities
6. When all received → "Received" status

**Example:**

```
PO-2025-0002
Shipment 1:
  Item 1: 10 ordered → 6 received (4 pending)
  Item 2: 5 ordered → 5 received ✓
  Status: Remains "Approved"

Shipment 2:
  Item 1: 4 remaining → 4 received ✓
  Status: Approved → Received
```

### Back-Order Receiving

**Some items unavailable**

**Process:**

1. Receive available items
2. Record partial receipt
3. Vendor notifies of back-order
4. Create new PO for back-ordered items OR
5. Cancel remaining quantity
6. Complete original PO

## API Endpoint

### Receive Items

```http
POST /api/v1/purchase-orders/{id}/receive
```

**Prerequisites:**

- PO must be in "Approved" status
- User must be Finance Officer
- Items must belong to the PO

**Request Body:**

```json
{
    "items": [
        {
            "item_id": 1,
            "quantity_received": 10
        },
        {
            "item_id": 2,
            "quantity_received": 5
        }
    ],
    "receipt_notes": "All items received in good condition",
    "received_date": "2025-11-20"
}
```

**Validation Rules:**

- `items`: Required, array, minimum 1 item
- `items.*.item_id`: Required, must exist in PO
- `items.*.quantity_received`: Required, integer, minimum 1, cannot exceed remaining quantity
- `receipt_notes`: Optional, max 1000 characters
- `received_date`: Optional, valid date, defaults to today

**Response (200 OK):**

```json
{
    "message": "Items received successfully",
    "purchase_order": {
        "id": 1,
        "po_number": "PO-2025-0001",
        "status": "Received",
        "items": [
            {
                "id": 1,
                "description": "A4 Copy Paper",
                "quantity": 10,
                "quantity_received": 10,
                "unit_price": "250.00",
                "total_price": "2500.00"
            },
            {
                "id": 2,
                "description": "Pens",
                "quantity": 5,
                "quantity_received": 5,
                "unit_price": "150.00",
                "total_price": "750.00"
            }
        ],
        "total_received": "3250.00"
    }
}
```

**Business Logic:**

1. Validates PO is in Approved status
2. Validates all item_ids belong to this PO
3. Checks received quantity doesn't exceed ordered quantity
4. Updates quantity_received for each item
5. Checks if all items are fully received:
    - If yes → status changes to "Received"
    - If no → status remains "Approved"
6. Records receipt date and notes
7. Triggers expense creation (if configured)
8. Sends notification to creator

**Error Responses:**

**Wrong Status (422):**

```json
{
    "message": "Cannot receive items. Purchase order must be in Approved status"
}
```

**Over-Receiving (422):**

```json
{
    "message": "Validation failed",
    "errors": {
        "items.0.quantity_received": [
            "Cannot receive 15 items. Only 10 ordered and 0 already received"
        ]
    }
}
```

**Invalid Item (422):**

```json
{
    "message": "Validation failed",
    "errors": {
        "items.0.item_id": ["Item does not belong to this purchase order"]
    }
}
```

## Receiving Calculations

### Remaining Quantity

```
Remaining = Quantity Ordered - Quantity Already Received
```

**Example:**

- Ordered: 20 boxes
- Already received: 12 boxes
- Remaining: 8 boxes
- Can receive: 1-8 boxes

### Receiving Validation

```
New Receipt + Already Received ≤ Quantity Ordered
```

**Valid Example:**

- Ordered: 10
- Already received: 6
- Trying to receive: 4
- Check: 4 + 6 = 10 ≤ 10 ✓ Valid

**Invalid Example:**

- Ordered: 10
- Already received: 6
- Trying to receive: 5
- Check: 5 + 6 = 11 > 10 ✗ Invalid

### Full Receipt Check

```
All Items Fully Received = Every Item(Quantity Received == Quantity Ordered)
```

If true → Status changes to "Received"

## User Interface

### Receiving Screen

**PO Header:**

- PO Number
- Vendor Name
- Order Date
- Total Amount
- Current Status

**Items Grid:**
| Description | Ordered | Already Received | Remaining | Receive Now |
|-------------|---------|------------------|-----------|-------------|
| A4 Paper | 10 | 6 | 4 | [Input: 4] |
| Pens | 20 | 0 | 20 | [Input: 20] |

**Actions:**

- Record Receipt button
- Add receipt notes
- Select receipt date
- Print receipt

**Visual Indicators:**

- ✓ Fully received items (green)
- ⚠ Partially received items (yellow)
- ○ Not yet received items (gray)

### Receipt History

Shows all receiving transactions:

- Receipt date
- Items received
- Quantities
- Who recorded
- Notes

## Business Rules

### Authorization

- Only Finance Officers can receive items
- Creator can receive own POs
- Delegated users can receive assigned POs

### Receiving Constraints

- Must be in "Approved" status
- Cannot receive zero quantity
- Cannot receive negative quantity
- Cannot receive more than ordered
- Cannot receive already-received items again

### Status Updates

**Partial Receipt:**

- Some items not fully received
- Status remains "Approved"
- Can receive more later

**Full Receipt:**

- All items fully received
- Status automatically changes to "Received"
- Ready for completion

### Receipt Documentation

Best practices:

- Add receipt notes
- Record actual delivery date
- Photograph delivery (if significant)
- Note any discrepancies
- Document damaged items

## Receipt Notes Guidelines

### Good Receipt Notes

✓ "All 10 boxes received in good condition. Signed by John Doe."
✓ "Partial delivery: 6 of 10 boxes received. Remaining 4 expected next week."
✓ "Received 20 reams. 2 boxes slightly damaged but contents intact."

### Insufficient Notes

✗ "Received"
✗ "OK"
✗ "Done"

### Important Information to Include

1. Condition of items
2. Any discrepancies
3. Delivery person name
4. Reason for partial delivery
5. Expected date for remaining items

## Discrepancy Handling

### Quantity Discrepancy

**Received less than ordered:**

1. Record actual quantity received
2. Note reason in receipt notes
3. Contact vendor about shortage
4. Options:
    - Wait for remaining items
    - Partial completion
    - Cancel remaining and adjust PO

**Example:**

```
Ordered: 20 boxes
Received: 18 boxes
Notes: "Vendor confirmed 2 boxes out of stock.
        Will ship next week or offer credit."
```

### Quality Issues

**Items damaged or defective:**

1. Do not record as received
2. Reject the items
3. Document with photos
4. Contact vendor
5. Arrange return/replacement
6. Update PO notes

**Example:**

```
Ordered: 10 laptops
Delivered: 10 laptops (1 damaged)
Recorded: 9 laptops received
Notes: "1 laptop screen cracked on delivery.
        Vendor arranging replacement."
```

### Wrong Items Delivered

**Received different items:**

1. Do not record receipt
2. Contact vendor immediately
3. Document discrepancy
4. Arrange return
5. Update expected delivery date

## Integration with Other Modules

### Expense Management

**Automatic Expense Creation:**

- When items received → create expense record
- Links to original PO
- Uses vendor as payee
- Amount = received items value
- Status = "Pending Payment"

**Example:**

```
PO-2025-0001 items received
→ Creates Expense Record:
   Description: "PO-2025-0001 - Office Supplies"
   Amount: $3,250.00
   Vendor: ABC Supplies
   Status: Pending Payment
   Due Date: Based on payment terms
```

### Inventory Management

**Stock Updates:**

- Received items → increase inventory
- Track by location
- Update stock levels
- Generate inventory reports

### Cash Flow

**Payment Scheduling:**

- Received items → payment due
- Calculate due date based on payment terms
- Update cash flow projections
- Allocate cash for payment

## Reporting

### Receiving Reports

**Goods Receipt Report:**

- All receipts for period
- By vendor
- By project
- By receiver

**Pending Receipts Report:**

- Approved POs not yet received
- Aging analysis
- Expected delivery dates
- Follow-up required

**Receiving Performance:**

- Average time to receive
- Full vs partial receipts
- Discrepancy rate
- Vendor delivery performance

## Best Practices

1. **Immediate Recording**: Record receipts same day as delivery
2. **Thorough Inspection**: Check quality and quantity before recording
3. **Detailed Notes**: Document condition, discrepancies, delivery person
4. **Proper Storage**: Store received items securely immediately
5. **Prompt Communication**: Notify stakeholders of receipt
6. **Evidence Retention**: Keep delivery notes, signatures, photos
7. **Regular Follow-up**: Monitor pending receipts weekly

## Common Use Cases

### Case 1: Standard Full Receipt

1. PO approved for 100 items
2. All 100 items delivered
3. Finance Officer inspects
4. All items acceptable
5. Record receipt of 100 items
6. PO status → Received
7. Complete the PO

### Case 2: Partial Delivery

1. PO approved for 50 items
2. Only 30 items delivered
3. Finance Officer records 30 items
4. PO status remains Approved
5. Later, 20 items delivered
6. Finance Officer records remaining 20
7. PO status → Received
8. Complete the PO

### Case 3: Damaged Items

1. PO approved for 20 items
2. 20 items delivered
3. 2 items damaged
4. Record only 18 items received
5. Add note about 2 damaged items
6. Contact vendor for replacement
7. When replacements arrive, record final 2
8. PO status → Received

## Troubleshooting

**Problem**: Cannot record receipt
**Solution**: Check PO status. Must be "Approved". Check user permissions (Finance Officer only).

**Problem**: Over-receiving error
**Solution**: Check how many already received. Cannot exceed ordered quantity. Use partial receiving.

**Problem**: Wrong item delivered
**Solution**: Do not record as received. Contact vendor. Document issue in PO notes.

**Problem**: PO not moving to "Received" status
**Solution**: Check if all items fully received. Even one item partially received keeps PO in "Approved" status.

**Problem**: Need to reverse a receipt
**Solution**: Contact system administrator. May require database adjustment. Prevention: double-check before recording.

## Mobile Receiving

For organizations with mobile capability:

- Scan delivery note barcode
- Take photos of items
- Record receipt on mobile device
- Add GPS location
- Capture delivery person signature
- Sync to main system

## Audit Trail

System automatically records:

- Who received items
- When items received
- Quantities received
- Any notes or comments
- IP address/location
- Before/after status
- All changes timestamped

Audit trail helps with:

- Accountability
- Fraud prevention
- Dispute resolution
- Performance analysis
- Compliance reporting
