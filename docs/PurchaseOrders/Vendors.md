# Vendor Management

## Overview

Vendor Management allows Finance Officers to maintain a comprehensive database of suppliers and service providers. Each vendor has a unique code, contact information, payment terms, and transaction history.

## Features

### Vendor Registration

Create new vendors with:

- **Vendor Code**: Auto-generated (VEN-YYYY-NNNN)
- **Name**: Company or individual name
- **Contact Person**: Primary contact
- **Email**: Contact email address
- **Phone**: Contact phone number
- **Address**: Physical/mailing address
- **Tax ID**: Tax identification number
- **Payment Terms**: Net 30, Net 60, COD, etc.
- **Status**: Active/Inactive

### Vendor Management

#### Activation/Deactivation

- Deactivate vendors no longer used
- Deactivated vendors cannot be selected for new POs
- Existing POs with deactivated vendors remain valid
- Reactivate vendors when needed

#### Vendor Summary

Each vendor provides:

- Total purchase orders
- Total order value
- Pending orders count
- Completed orders count
- Recent 5 orders
- Payment history

## API Endpoints

### List Vendors

```http
GET /api/v1/vendors
```

**Query Parameters:**

- `is_active` (boolean): Filter by active status
- `search` (string): Search vendor code, name, email, or contact person
- `per_page` (integer): Items per page (default: 15)

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "vendor_code": "VEN-2025-0001",
      "name": "ABC Office Supplies Ltd",
      "contact_person": "John Doe",
      "email": "john@abcsupplies.com",
      "phone": "+260 211 123456",
      "address": "123 Independence Ave, Lusaka",
      "tax_id": "1234567890",
      "payment_terms": "Net 30",
      "is_active": true,
      "created_at": "2025-01-10T10:00:00.000000Z",
      "updated_at": "2025-11-15T14:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create Vendor

```http
POST /api/v1/vendors
```

**Request Body:**

```json
{
    "name": "ABC Office Supplies Ltd",
    "contact_person": "John Doe",
    "email": "john@abcsupplies.com",
    "phone": "+260 211 123456",
    "address": "123 Independence Ave, Lusaka",
    "tax_id": "1234567890",
    "payment_terms": "Net 30"
}
```

**Validation Rules:**

- `name`: Required, max 255 characters
- `contact_person`: Optional, max 255 characters
- `email`: Optional, valid email, unique across vendors
- `phone`: Optional, max 50 characters
- `address`: Optional, max 500 characters
- `tax_id`: Optional, max 50 characters
- `payment_terms`: Optional, max 255 characters

**Response (201 Created):**

```json
{
  "message": "Vendor created successfully",
  "vendor": {
    "id": 1,
    "vendor_code": "VEN-2025-0001",
    "name": "ABC Office Supplies Ltd",
    ...
  }
}
```

**Business Logic:**

1. Validates all input fields
2. Checks email uniqueness
3. Generates vendor code (VEN-YYYY-NNNN)
4. Sets is_active = true
5. Creates vendor record
6. Returns vendor details

### View Vendor

```http
GET /api/v1/vendors/{id}
```

**Response:**

```json
{
    "vendor": {
        "id": 1,
        "vendor_code": "VEN-2025-0001",
        "name": "ABC Office Supplies Ltd",
        "contact_person": "John Doe",
        "email": "john@abcsupplies.com",
        "phone": "+260 211 123456",
        "address": "123 Independence Ave, Lusaka",
        "tax_id": "1234567890",
        "payment_terms": "Net 30",
        "is_active": true,
        "created_at": "2025-01-10T10:00:00.000000Z",
        "updated_at": "2025-11-15T14:00:00.000000Z"
    }
}
```

### Update Vendor

```http
PUT /api/v1/vendors/{id}
```

**Request Body:**

```json
{
    "name": "ABC Office Supplies Limited",
    "contact_person": "Jane Smith",
    "email": "jane@abcsupplies.com",
    "phone": "+260 211 654321",
    "address": "456 New Address, Lusaka",
    "payment_terms": "Net 60"
}
```

**Note:** Vendor code cannot be changed. All other fields are updatable.

**Response:**

```json
{
  "message": "Vendor updated successfully",
  "vendor": {...}
}
```

### Deactivate Vendor

```http
POST /api/v1/vendors/{id}/deactivate
```

**Response:**

```json
{
  "message": "Vendor deactivated successfully",
  "vendor": {
    "id": 1,
    "is_active": false,
    ...
  }
}
```

**Business Logic:**

- Sets is_active = false
- Vendor cannot be selected for new POs
- Existing POs remain valid

### Activate Vendor

```http
POST /api/v1/vendors/{id}/activate
```

**Response:**

```json
{
  "message": "Vendor activated successfully",
  "vendor": {
    "id": 1,
    "is_active": true,
    ...
  }
}
```

### Vendor Summary

```http
GET /api/v1/vendors/{id}/summary
```

**Response:**

```json
{
    "vendor": {
        "id": 1,
        "vendor_code": "VEN-2025-0001",
        "name": "ABC Office Supplies Ltd"
    },
    "total_orders": 25,
    "total_value": "250000.00",
    "pending_orders": 3,
    "completed_orders": 20,
    "recent_orders": [
        {
            "id": 123,
            "po_number": "PO-2025-0123",
            "order_date": "2025-11-01",
            "total_amount": "15000.00",
            "status": "Approved"
        }
    ]
}
```

### Delete Vendor

```http
DELETE /api/v1/vendors/{id}
```

**Response:**

```json
{
    "message": "Vendor deleted successfully"
}
```

**Business Logic:**

- Cannot delete vendor with existing purchase orders
- Uses soft delete (data retained)
- Returns error if POs exist

**Error Response (422):**

```json
{
    "message": "Cannot delete vendor with existing purchase orders"
}
```

## Business Rules

### Vendor Code Generation

- Format: VEN-YYYY-NNNN
- Year: Current year
- Sequential number: Auto-incremented
- Example: VEN-2025-0001
- Cannot be manually set

### Email Uniqueness

- Each email address must be unique
- System validates before creating/updating
- Prevents duplicate vendor registration

### Deactivation Rules

- Only Finance Officers can deactivate
- Deactivated vendors not shown in PO vendor dropdown
- Cannot delete vendors with PO history
- Can reactivate anytime

### Payment Terms

Common payment terms:

- **Net 30**: Payment due 30 days after invoice
- **Net 60**: Payment due 60 days after invoice
- **Net 90**: Payment due 90 days after invoice
- **COD**: Cash on delivery
- **2/10 Net 30**: 2% discount if paid within 10 days, else net 30
- **Custom**: Any other terms as text

## User Interface

### Vendor List View

- Paginated list with search
- Filter by active/inactive
- Quick actions (view, edit, deactivate)
- Vendor code prominently displayed
- Contact information preview

### Vendor Detail View

- Complete vendor information
- Purchase order history
- Total order value
- Payment performance
- Quick actions

### Create/Edit Form

- All vendor fields
- Email validation
- Payment terms dropdown
- Active status toggle
- Save and Continue option

## Best Practices

1. **Complete Information**: Fill all available fields for better tracking
2. **Valid Contacts**: Ensure email and phone are current
3. **Payment Terms**: Clearly define payment expectations
4. **Regular Updates**: Review and update vendor info quarterly
5. **Deactivate Unused**: Deactivate vendors no longer used
6. **Tax ID**: Record tax IDs for compliance

## Common Use Cases

### Adding a New Vendor

1. Navigate to Vendors
2. Click "Add New Vendor"
3. Enter vendor name
4. Add contact information
5. Set payment terms
6. Save vendor
7. System generates vendor code

### Searching for a Vendor

1. Use search box
2. Enter vendor code, name, or email
3. Or filter by active status
4. Click on vendor to view details

### Deactivating a Vendor

1. Find vendor in list
2. Click "Deactivate"
3. Confirm action
4. Vendor status changes to inactive
5. Cannot select for new POs

### Viewing Vendor Performance

1. Click on vendor name
2. View summary tab
3. See total orders and value
4. Review recent orders
5. Check payment history

## Reporting

### Vendor List Report

- All vendors with key information
- Filter by active status
- Export to Excel/PDF

### Vendor Performance Report

- Orders per vendor
- Value per vendor
- Payment performance
- Delivery performance

### Vendor Analysis

- Top vendors by volume
- Top vendors by value
- Vendor concentration analysis
- New vendor trends

## Troubleshooting

**Problem**: Cannot create vendor - "Email already exists"
**Solution**: Each vendor email must be unique. Check if vendor already exists or use different email.

**Problem**: Cannot delete vendor
**Solution**: Vendors with purchase orders cannot be deleted. Deactivate instead.

**Problem**: Vendor not appearing in PO dropdown
**Solution**: Check if vendor is active. Only active vendors appear in dropdown.

**Problem**: Vendor code not generated
**Solution**: System auto-generates vendor codes. Do not enter manually.
