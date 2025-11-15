# Purchase Order Management Module

## Overview

The Purchase Order Management module provides a complete procurement workflow for CANZIM FinTrack. It enables organizations to manage vendor relationships, create purchase orders, track approvals, receive goods/services, and complete the procurement cycle with full audit trails.

## Module Components

### 1. Vendor Management

- Vendor master data maintenance
- Auto-generated vendor codes
- Contact information management
- Payment terms configuration
- Vendor activation/deactivation

### 2. Purchase Order Workflow

- Draft purchase orders creation
- Multi-level approval workflow
- Item-level tracking
- Receiving management
- Order completion

### 3. Order Lifecycle

```
Draft → Submit → Pending → Approve/Reject → Approved → Receive → Completed
         ↓                      ↓
      (editable)           (rejection path)
```

## Key Features

### Auto-Numbering

- **PO Numbers**: PO-YYYY-NNNN (e.g., PO-2025-0001)
- **Vendor Codes**: VEN-YYYY-NNNN (e.g., VEN-2025-0001)
- Sequential numbering per year
- Unique across the system

### Multi-Level Approval

- **Creator**: Project/Finance Officer creates draft
- **Submitter**: Submits for approval
- **Approver**: Programs Manager approves/rejects
- **Receiver**: Finance Officer marks items received

### Item Management

- Multiple items per purchase order
- Quantity tracking (ordered vs received)
- Unit price and total calculation
- Partial receiving support
- Over-receiving prevention

### Financial Integration

- Automatic subtotal calculation
- Configurable tax calculation
- Total amount tracking
- Project budget integration
- Expense linking

## User Roles & Permissions

### Finance Officer

- Create and manage purchase orders
- Submit for approval
- Receive goods/services
- Mark orders as completed
- Manage vendors
- View all purchase orders

### Programs Manager

- View all purchase orders
- Approve purchase orders
- Reject purchase orders with reason
- View vendor information

### Project Officer

- Create purchase orders for assigned projects
- Submit for approval
- View own purchase orders only
- Cannot approve orders

### Executive Director

- View all purchase orders
- View reports and statistics
- Read-only access

## Workflow States

### Draft

- Initial state when PO is created
- Fully editable
- Can be deleted
- Not visible to approvers

### Pending

- Submitted for approval
- Read-only (cannot edit)
- Visible to approvers
- Can be approved or rejected

### Approved

- Approved by Programs Manager
- Ready for receiving
- Cannot be edited
- Can receive items

### Rejected

- Rejected by Programs Manager
- Includes rejection reason
- Can be viewed but not edited
- Requires new PO creation

### Received

- All items marked as received
- Awaiting completion
- Cannot modify items

### Completed

- Final state
- All items received and verified
- Order closed
- Historical record only

### Cancelled

- Order cancelled before completion
- Includes cancellation reason
- Cannot be reactivated

## Integration Points

### Projects Module

- Link POs to specific projects
- Track project procurement
- Project-level PO reporting
- Budget impact tracking

### Expense Management

- Convert received POs to expenses
- Automatic expense creation
- Invoice matching
- Payment tracking

### Vendors

- Vendor performance tracking
- Order history by vendor
- Payment terms enforcement
- Vendor analytics

### Cash Flow

- Impact on cash projections
- Payment scheduling
- Cash flow planning
- Liquidity management

## Technical Details

### Database Tables

- `vendors` - Vendor master data
- `purchase_orders` - PO header information
- `purchase_order_items` - PO line items

### API Endpoints

See individual documentation files:

- [Vendor API](VendorAPI.md)
- [Purchase Order API](PurchaseOrderAPI.md)

### Business Rules

See [Business Rules](BusinessRules.md) for detailed validation and workflow rules.

## Getting Started

1. **Set up Vendors**: Create vendor records for suppliers
2. **Create Purchase Orders**: Draft POs for procurement needs
3. **Submit for Approval**: Send to Programs Manager
4. **Approve/Reject**: Programs Manager reviews and decides
5. **Receive Items**: Mark items as received when delivered
6. **Complete Order**: Finalize the procurement cycle

## Common Workflows

### Standard Purchase Order Workflow

1. Finance Officer creates PO in Draft status
2. Adds items with quantities and prices
3. Submits PO for approval (moves to Pending)
4. Programs Manager reviews and approves
5. Goods/services are delivered
6. Finance Officer receives items (partial or full)
7. All items received → status changes to Received
8. Finance Officer marks as Completed

### Rejection Workflow

1. Programs Manager rejects PO
2. Adds rejection reason
3. Creator receives notification
4. Creator reviews reason
5. Creator creates new PO with corrections

### Partial Receiving Workflow

1. Some items delivered
2. Finance Officer receives those items
3. PO remains in Approved status
4. Later, remaining items delivered
5. Finance Officer receives remaining items
6. All received → Received status
7. Complete the order

## Reporting & Analytics

### Purchase Order Statistics

- Total orders by status
- Total value of orders
- Recent orders
- Approval rates
- Average processing time

### Vendor Analytics

- Total orders per vendor
- Total value per vendor
- Pending vs completed orders
- Vendor performance metrics

### Project Reporting

- Procurement by project
- Budget vs actual spending
- Open commitments
- Completed purchases

## Best Practices

1. **Vendor Setup**: Maintain complete vendor information
2. **Clear Descriptions**: Use detailed item descriptions
3. **Accurate Quantities**: Double-check quantities before submission
4. **Timely Approvals**: Review and approve within 48 hours
5. **Prompt Receiving**: Record receipts immediately upon delivery
6. **Documentation**: Attach invoices and delivery notes
7. **Regular Reviews**: Monitor open POs weekly

## Related Documentation

- [Vendor Management](Vendors.md)
- [Purchase Order Workflow](PurchaseOrderWorkflow.md)
- [Item Receiving](ItemReceiving.md)
- [API Reference](API.md)

## Support

For technical support or questions about the Purchase Order module, contact the CANZIM FinTrack support team.
