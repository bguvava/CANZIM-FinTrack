# Expense Management API Reference

## Base URL

```
/api/v1/expenses
```

## Authentication

All endpoints require authentication via Laravel Sanctum tokens.

## Endpoints

### 1. List Expenses

```http
GET /api/v1/expenses
```

**Description**: Retrieve paginated list of expenses with optional filters

**Query Parameters**:

- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15)
- `status` (string): Filter by status (Draft, Submitted, Under Review, Approved, Rejected, Paid)
- `project_id` (integer): Filter by project
- `expense_category_id` (integer): Filter by category
- `date_from` (date): Start date filter (Y-m-d format)
- `date_to` (date): End date filter (Y-m-d format)
- `search` (string): Search in expense number and description

**Authorization**:

- Project Officers: See only own expenses
- Finance Officers & Programs Managers: See all expenses

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 1,
      "expense_number": "EXP-2025-0001",
      "project_id": 5,
      "expense_category_id": 2,
      "budget_item_id": 10,
      "amount": 500.00,
      "expense_date": "2025-11-15",
      "description": "Travel to field site",
      "status": "Submitted",
      "receipt_path": "receipts/2025/11/uuid-filename.pdf",
      "submitted_by": 3,
      "created_at": "2025-11-15T08:30:00.000000Z",
      "project": {...},
      "category": {...},
      "submitter": {...}
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 45,
  "last_page": 3
}
```

---

### 2. Create Expense

```http
POST /api/v1/expenses
```

**Description**: Create a new expense in Draft status

**Authorization**: All authenticated users

**Request Body** (multipart/form-data):

```json
{
    "project_id": 5,
    "budget_item_id": 10,
    "expense_category_id": 2,
    "expense_date": "2025-11-15",
    "amount": 500.0,
    "description": "Travel to field site for monitoring",
    "receipt": "<file>" // Optional, PDF/JPG/PNG, max 5MB
}
```

**Validation Rules**:

- `project_id`: required, exists in projects table
- `budget_item_id`: nullable, exists in budget_items table
- `expense_category_id`: required, exists in expense_categories table
- `expense_date`: required, date, before or equal to today
- `amount`: required, numeric, min:0.01
- `description`: required, string, max:1000
- `receipt`: nullable, file, mimes:pdf,jpg,jpeg,png, max:5120 (5MB)

**Response** (201 Created):

```json
{
  "message": "Expense created successfully",
  "expense": {
    "id": 1,
    "expense_number": "EXP-2025-0001",
    "status": "Draft",
    ...
  }
}
```

---

### 3. Get Expense Details

```http
GET /api/v1/expenses/{id}
```

**Description**: Retrieve detailed information about a specific expense

**Authorization**:

- Project Officers: Own expenses only
- Finance Officers & Programs Managers: All expenses

**Response** (200 OK):

```json
{
  "id": 1,
  "expense_number": "EXP-2025-0001",
  "project": {...},
  "category": {...},
  "budget_item": {...},
  "submitter": {...},
  "reviewer": {...},
  "approver": {...},
  "approvals": [
    {
      "id": 1,
      "approval_level": 1,
      "action": "approved",
      "comments": "Approved by Finance Officer",
      "user": {...},
      "created_at": "2025-11-15T09:00:00.000000Z"
    }
  ],
  ...
}
```

---

### 4. Update Expense

```http
PUT /api/v1/expenses/{id}
```

**Description**: Update expense details (only for Draft or Rejected status)

**Authorization**: Submitter only, expense must be Draft or Rejected

**Request Body** (multipart/form-data):

```json
{
    "expense_category_id": 3,
    "amount": 550.0,
    "description": "Updated description",
    "receipt": "<file>" // Optional, replaces existing
}
```

**Validation**: Same as Create, but all fields use 'sometimes' modifier

**Response** (200 OK):

```json
{
  "message": "Expense updated successfully",
  "expense": {...}
}
```

---

### 5. Delete Expense

```http
DELETE /api/v1/expenses/{id}
```

**Description**: Soft delete an expense

**Authorization**:

- Submitter: Draft status only
- Programs Manager: Draft status only

**Response** (200 OK):

```json
{
    "message": "Expense deleted successfully"
}
```

---

### 6. Submit Expense

```http
POST /api/v1/expenses/{id}/submit
```

**Description**: Submit expense for Finance Officer review (Draft → Submitted)

**Authorization**: Submitter only, expense must be Draft or Rejected

**Response** (200 OK):

```json
{
    "message": "Expense submitted for approval",
    "expense": {
        "id": 1,
        "status": "Submitted",
        "submitted_at": "2025-11-15T10:00:00.000000Z"
    }
}
```

**Side Effects**:

- Creates approval record (level 1)
- Sends notification to Finance Officers
- Updates submitted_at timestamp

---

### 7. Review Expense (Finance Officer)

```http
POST /api/v1/expenses/{id}/review
```

**Description**: Finance Officer reviews submitted expense

**Authorization**: Finance Officer only, expense must be Submitted

**Request Body**:

```json
{
    "action": "approve", // or "reject"
    "comments": "Approved for manager review" // Optional for approve, required for reject
}
```

**Response** (200 OK):

```json
{
  "message": "Expense forwarded to Programs Manager", // or "Expense returned for revision"
  "expense": {
    "id": 1,
    "status": "Under Review", // or "Rejected"
    ...
  }
}
```

**Side Effects** (if approved):

- Status: Submitted → Under Review
- Creates approval record (level 2)
- Notifies Programs Manager
- Records reviewed_by, reviewed_at

**Side Effects** (if rejected):

- Status: Submitted → Rejected
- Records rejection_reason, rejected_by, rejected_at
- Notifies submitter

---

### 8. Approve/Reject Expense (Programs Manager)

```http
POST /api/v1/expenses/{id}/approve
```

**Description**: Programs Manager final approval or rejection

**Authorization**: Programs Manager only, expense must be Under Review

**Request Body**:

```json
{
    "action": "approve", // or "reject"
    "comments": "Approved for payment" // Optional
}
```

**Response** (200 OK):

```json
{
  "message": "Expense approved successfully", // or "Expense rejected"
  "expense": {
    "id": 1,
    "status": "Approved", // or "Rejected"
    ...
  }
}
```

**Side Effects** (if approved):

- Status: Under Review → Approved
- Creates approval record (level 3)
- Updates budget spent_amount
- Notifies submitter
- Records approved_by, approved_at

**Side Effects** (if rejected):

- Status: Under Review → Rejected
- Records rejection_reason, rejected_by, rejected_at
- Notifies submitter

---

### 9. Mark as Paid

```http
POST /api/v1/expenses/{id}/mark-paid
```

**Description**: Finance Officer marks approved expense as paid

**Authorization**: Finance Officer only, expense must be Approved

**Request Body**:

```json
{
    "payment_reference": "PAY-2025-0001",
    "payment_method": "Bank Transfer", // or "Check", "Cash", "Mobile Money"
    "payment_notes": "Paid via bank transfer" // Optional
}
```

**Validation Rules**:

- `payment_reference`: required, string, max:100
- `payment_method`: required, in:Bank Transfer,Check,Cash,Mobile Money
- `payment_notes`: nullable, string, max:500

**Response** (200 OK):

```json
{
    "message": "Expense marked as paid",
    "expense": {
        "id": 1,
        "status": "Paid",
        "payment_reference": "PAY-2025-0001",
        "paid_at": "2025-11-15T14:00:00.000000Z"
    }
}
```

**Side Effects**:

- Status: Approved → Paid
- Records payment_reference, payment_method, payment_notes
- Records paid_by, paid_at

---

### 10. Get Pending Review (Finance Officer)

```http
GET /api/v1/expenses/pending-review
```

**Description**: List expenses awaiting Finance Officer review

**Authorization**: Finance Officer only

**Response** (200 OK):

```json
[
  {
    "id": 1,
    "expense_number": "EXP-2025-0001",
    "status": "Submitted",
    "amount": 500.00,
    "submitter": {...},
    ...
  }
]
```

---

### 11. Get Pending Approval (Programs Manager)

```http
GET /api/v1/expenses/pending-approval
```

**Description**: List expenses awaiting Programs Manager approval

**Authorization**: Programs Manager only

**Response** (200 OK):

```json
[
  {
    "id": 2,
    "expense_number": "EXP-2025-0002",
    "status": "Under Review",
    "amount": 750.00,
    "submitter": {...},
    "reviewer": {...},
    ...
  }
]
```

---

### 12. Get Expense Categories

```http
GET /api/v1/expenses/categories
```

**Description**: List all active expense categories

**Authorization**: All authenticated users

**Response** (200 OK):

```json
[
  {
    "id": 1,
    "code": "TRAVEL",
    "name": "Travel & Transportation",
    "description": "Travel expenses including flights, accommodation, per diem",
    "is_active": true,
    "sort_order": 1
  },
  ...
]
```

---

## Error Responses

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
    "message": "Expense not found."
}
```

### 422 Validation Error

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "amount": ["The amount must be at least 0.01."],
        "receipt": ["The receipt must not be greater than 5120 kilobytes."]
    }
}
```

### 500 Server Error

```json
{
    "message": "Error creating expense",
    "error": "Detailed error message"
}
```

---

## Status Flow Diagram

```
Draft ──submit──> Submitted ──review(approve)──> Under Review ──approve──> Approved ──mark-paid──> Paid
  │                   │                              │
  │                   │                              │
  └─────delete────────┘                              │
                      │                              │
                      └──review(reject)──> Rejected <─┘
                                              │
                                              └──submit──> Submitted
```

## Rate Limiting

All API endpoints are subject to Laravel's default rate limiting (60 requests per minute per user).

---

**Last Updated**: 2025-11-15
**API Version**: 1.0
