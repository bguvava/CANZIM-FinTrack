# Purchase Order Workflow

## Overview

Purchase Orders (POs) follow a structured workflow from creation through completion. The workflow ensures proper authorization, tracking, and accountability throughout the procurement process.

## Workflow States

### Draft

**Initial state when PO is created**

**Characteristics:**

- Fully editable
- Can be deleted
- Not visible to approvers
- No financial commitment

**Available Actions:**

- Edit all fields
- Add/remove items
- Submit for approval
- Delete PO

**Who Can See:**

- Creator only
- Finance Officers (if creator is different)

### Pending

**Submitted for approval**

**Characteristics:**

- Read-only (cannot edit)
- Visible to approvers
- Awaiting approval decision
- Financial commitment pending

**Available Actions:**

- Approve (Programs Manager)
- Reject (Programs Manager)
- View only (Creator)
- Cancel (Finance Officer)

**Who Can See:**

- Creator
- Finance Officers
- Programs Manager
- Executive Director

### Approved

**Approved and ready for receiving**

**Characteristics:**

- Approved by Programs Manager
- Cannot edit PO details
- Can receive items
- Financial commitment made

**Available Actions:**

- Receive items (partial or full)
- Cancel with reason
- View details

**Auto-Transitions:**

- → Received (when all items received)

### Rejected

**Rejected by approver**

**Characteristics:**

- Includes rejection reason
- Cannot be modified
- Must create new PO

**Available Actions:**

- View only
- Create new PO

**Best Practice:**

- Review rejection reason
- Correct issues
- Create new PO

### Received

**All items marked as received**

**Characteristics:**

- All items received
- Ready for completion
- Cannot modify items

**Available Actions:**

- Mark as Completed
- View details
- Generate expense records

### Completed

**Final state - order closed**

**Characteristics:**

- All items received and verified
- Order finalized
- Historical record only

**Available Actions:**

- View only
- Export/Print

### Cancelled

**Order cancelled before completion**

**Characteristics:**

- Includes cancellation reason
- Cannot be reactivated
- Reverses financial commitment

**Available Actions:**

- View only

## API Endpoints

### List Purchase Orders

```http
GET /api/v1/purchase-orders
```

**Query Parameters:**

- `status` (string): Draft, Pending, Approved, Rejected, Received, Completed, Cancelled
- `vendor_id` (integer): Filter by vendor
- `project_id` (integer): Filter by project
- `created_by` (integer): Filter by creator
- `date_from` (date): Orders from this date
- `date_to` (date): Orders until this date
- `search` (string): Search PO number or vendor name
- `per_page` (integer): Items per page (default: 15)

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "po_number": "PO-2025-0001",
      "vendor_id": 5,
      "vendor": {
        "id": 5,
        "vendor_code": "VEN-2025-0005",
        "name": "ABC Supplies"
      },
      "project_id": 10,
      "project": {
        "id": 10,
        "project_code": "PROJ-2025-010",
        "name": "Water Project"
      },
      "order_date": "2025-11-15",
      "status": "Approved",
      "subtotal": "10000.00",
      "tax_amount": "1600.00",
      "total_amount": "11600.00",
      "notes": "Office supplies for Q4",
      "created_by": 3,
      "creator": {
        "id": 3,
        "name": "John Finance"
      },
      "approved_by": 2,
      "approver": {
        "id": 2,
        "name": "Jane Manager"
      },
      "approved_at": "2025-11-16T10:30:00.000000Z",
      "items_count": 5,
      "created_at": "2025-11-15T09:00:00.000000Z",
      "updated_at": "2025-11-16T10:30:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create Purchase Order

```http
POST /api/v1/purchase-orders
```

**Request Body:**

```json
{
    "vendor_id": 5,
    "project_id": 10,
    "order_date": "2025-11-15",
    "tax_rate": 16,
    "notes": "Office supplies for Q4",
    "items": [
        {
            "description": "A4 Copy Paper (Box of 5 reams)",
            "quantity": 10,
            "unit_price": "250.00"
        },
        {
            "description": "Blue Ballpoint Pens (Box of 50)",
            "quantity": 5,
            "unit_price": "150.00"
        }
    ]
}
```

**Validation Rules:**

- `vendor_id`: Required, must exist and be active
- `project_id`: Required, must exist
- `order_date`: Required, valid date
- `tax_rate`: Optional, numeric between 0-100
- `notes`: Optional, max 1000 characters
- `items`: Required, array, minimum 1 item
- `items.*.description`: Required, max 500 characters
- `items.*.quantity`: Required, integer, minimum 1
- `items.*.unit_price`: Required, numeric, minimum 0.01

**Response (201 Created):**

```json
{
    "message": "Purchase order created successfully",
    "purchase_order": {
        "id": 1,
        "po_number": "PO-2025-0001",
        "status": "Draft",
        "subtotal": "3250.00",
        "tax_amount": "520.00",
        "total_amount": "3770.00",
        "items": [
            {
                "id": 1,
                "description": "A4 Copy Paper (Box of 5 reams)",
                "quantity": 10,
                "unit_price": "250.00",
                "total_price": "2500.00",
                "quantity_received": 0
            },
            {
                "id": 2,
                "description": "Blue Ballpoint Pens (Box of 50)",
                "quantity": 5,
                "unit_price": "150.00",
                "total_price": "750.00",
                "quantity_received": 0
            }
        ]
    }
}
```

**Business Logic:**

1. Validates vendor is active
2. Validates project exists
3. Generates PO number (PO-YYYY-NNNN)
4. Creates PO with status = Draft
5. Calculates subtotal from items
6. Calculates tax amount
7. Calculates total amount
8. Creates PO items
9. Sets created_by to authenticated user

### View Purchase Order

```http
GET /api/v1/purchase-orders/{id}
```

**Response:**

```json
{
  "purchase_order": {
    "id": 1,
    "po_number": "PO-2025-0001",
    "vendor_id": 5,
    "vendor": {...},
    "project_id": 10,
    "project": {...},
    "order_date": "2025-11-15",
    "status": "Approved",
    "subtotal": "3250.00",
    "tax_amount": "520.00",
    "total_amount": "3770.00",
    "notes": "Office supplies for Q4",
    "items": [
      {
        "id": 1,
        "description": "A4 Copy Paper",
        "quantity": 10,
        "unit_price": "250.00",
        "total_price": "2500.00",
        "quantity_received": 0
      }
    ],
    "created_by": 3,
    "creator": {...},
    "approved_by": 2,
    "approver": {...},
    "approved_at": "2025-11-16T10:30:00.000000Z",
    "created_at": "2025-11-15T09:00:00.000000Z",
    "updated_at": "2025-11-16T10:30:00.000000Z"
  }
}
```

### Update Purchase Order

```http
PUT /api/v1/purchase-orders/{id}
```

**Can Only Update in Draft Status**

**Request Body:**

```json
{
    "vendor_id": 6,
    "project_id": 11,
    "order_date": "2025-11-16",
    "tax_rate": 18,
    "notes": "Updated notes",
    "items": [
        {
            "description": "Updated item description",
            "quantity": 15,
            "unit_price": "300.00"
        }
    ]
}
```

**Response:**

```json
{
  "message": "Purchase order updated successfully",
  "purchase_order": {...}
}
```

**Error if Not Draft (422):**

```json
{
    "message": "Only draft purchase orders can be updated"
}
```

### Submit for Approval

```http
POST /api/v1/purchase-orders/{id}/submit
```

**Only Draft Status Can Be Submitted**

**Response:**

```json
{
  "message": "Purchase order submitted for approval",
  "purchase_order": {
    "id": 1,
    "status": "Pending",
    ...
  }
}
```

**Business Logic:**

- Changes status from Draft → Pending
- Records submission timestamp
- Triggers notification to approvers

### Approve Purchase Order

```http
POST /api/v1/purchase-orders/{id}/approve
```

**Only Programs Manager Can Approve**
**Only Pending Status Can Be Approved**

**Response:**

```json
{
  "message": "Purchase order approved successfully",
  "purchase_order": {
    "id": 1,
    "status": "Approved",
    "approved_by": 2,
    "approved_at": "2025-11-16T10:30:00.000000Z",
    ...
  }
}
```

**Business Logic:**

- Changes status from Pending → Approved
- Records approver ID
- Records approval timestamp
- Triggers notification to creator

### Reject Purchase Order

```http
POST /api/v1/purchase-orders/{id}/reject
```

**Request Body:**

```json
{
    "rejection_reason": "Budget not available for this project"
}
```

**Validation:**

- `rejection_reason`: Required, max 1000 characters

**Response:**

```json
{
  "message": "Purchase order rejected",
  "purchase_order": {
    "id": 1,
    "status": "Rejected",
    "rejection_reason": "Budget not available for this project",
    "rejected_by": 2,
    "rejected_at": "2025-11-16T11:00:00.000000Z",
    ...
  }
}
```

### Receive Items

```http
POST /api/v1/purchase-orders/{id}/receive
```

**Only Approved Status Can Receive**

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
            "quantity_received": 3
        }
    ]
}
```

**Validation Rules:**

- `items`: Required, array
- `items.*.item_id`: Required, must belong to this PO
- `items.*.quantity_received`: Required, integer, minimum 1

**Business Logic:**

1. Validates quantities don't exceed ordered amounts
2. Updates quantity_received for each item
3. Checks if all items fully received
4. If all received → status changes to Received
5. If partial → status remains Approved

**Response:**

```json
{
    "message": "Items received successfully",
    "purchase_order": {
        "id": 1,
        "status": "Received",
        "items": [
            {
                "id": 1,
                "quantity": 10,
                "quantity_received": 10
            },
            {
                "id": 2,
                "quantity": 5,
                "quantity_received": 3
            }
        ]
    }
}
```

**Error if Over-Receiving (422):**

```json
{
    "message": "Cannot receive more than ordered quantity",
    "errors": {
        "items.0.quantity_received": [
            "Received quantity (15) exceeds ordered quantity (10)"
        ]
    }
}
```

### Complete Purchase Order

```http
POST /api/v1/purchase-orders/{id}/complete
```

**Only Received Status Can Be Completed**

**Response:**

```json
{
  "message": "Purchase order completed successfully",
  "purchase_order": {
    "id": 1,
    "status": "Completed",
    "completed_at": "2025-11-20T14:00:00.000000Z",
    ...
  }
}
```

### Cancel Purchase Order

```http
POST /api/v1/purchase-orders/{id}/cancel
```

**Can Cancel: Draft, Pending, Approved**
**Cannot Cancel: Received, Completed, Cancelled**

**Request Body:**

```json
{
    "cancellation_reason": "Project cancelled by donor"
}
```

**Response:**

```json
{
  "message": "Purchase order cancelled successfully",
  "purchase_order": {
    "id": 1,
    "status": "Cancelled",
    "cancellation_reason": "Project cancelled by donor",
    ...
  }
}
```

### Purchase Order Statistics

```http
GET /api/v1/purchase-orders/statistics
```

**Query Parameters:**

- `date_from` (date): Filter from date
- `date_to` (date): Filter to date
- `project_id` (integer): Filter by project

**Response:**

```json
{
  "total_orders": 50,
  "total_value": "500000.00",
  "by_status": {
    "Draft": 5,
    "Pending": 3,
    "Approved": 10,
    "Received": 8,
    "Completed": 22,
    "Rejected": 1,
    "Cancelled": 1
  },
  "value_by_status": {
    "Approved": "100000.00",
    "Received": "80000.00",
    "Completed": "300000.00"
  },
  "recent_orders": [...]
}
```

### Delete Purchase Order

```http
DELETE /api/v1/purchase-orders/{id}
```

**Can Only Delete Draft Status**

**Response:**

```json
{
    "message": "Purchase order deleted successfully"
}
```

**Error if Not Draft (422):**

```json
{
    "message": "Only draft purchase orders can be deleted"
}
```

## Business Rules

### PO Number Generation

- Format: PO-YYYY-NNNN
- Auto-generated on creation
- Cannot be changed
- Sequential per year

### Status Transitions

Valid transitions:

- Draft → Pending (submit)
- Pending → Approved (approve)
- Pending → Rejected (reject)
- Approved → Received (all items received)
- Received → Completed (finalize)
- Draft/Pending/Approved → Cancelled (cancel)

Invalid transitions trigger errors.

### Item Calculations

```
Item Total = Quantity × Unit Price
Subtotal = Sum of all Item Totals
Tax Amount = Subtotal × (Tax Rate / 100)
Total Amount = Subtotal + Tax Amount
```

### Receiving Rules

- Can only receive in Approved status
- Cannot receive more than ordered
- Partial receiving allowed
- Auto-transition to Received when all items received
- Cannot modify received quantities

### Edit Restrictions

- **Draft**: All fields editable
- **Pending**: Read-only
- **Approved**: Read-only (can receive items)
- **Other statuses**: Read-only

### Delete Restrictions

- Can only delete Draft status
- Cannot delete POs with other statuses
- Use soft delete (data retained)

## Common Workflows

See [README](README.md) for detailed workflow examples.

## Best Practices

1. **Complete Items**: Add all items before submitting
2. **Accurate Quantities**: Verify quantities carefully
3. **Clear Descriptions**: Use detailed item descriptions
4. **Timely Submission**: Submit within 24 hours of creation
5. **Prompt Approval**: Approve/reject within 48 hours
6. **Immediate Receiving**: Record receipts same day
7. **Regular Review**: Monitor pending/approved POs weekly

## Troubleshooting

**Problem**: Cannot submit PO
**Solution**: Ensure PO is in Draft status and has at least one item.

**Problem**: Cannot approve PO
**Solution**: Only Programs Managers can approve, and only Pending POs.

**Problem**: Cannot receive items
**Solution**: PO must be in Approved status. Check status first.

**Problem**: Over-receiving error
**Solution**: Cannot receive more than ordered. Check ordered vs already received quantities.

**Problem**: Cannot complete PO
**Solution**: All items must be fully received first.
