# Cash Flow Transactions

## Overview

Cash Flow Transactions track all money movements in and out of bank accounts. The system supports recording inflows (money received) and outflows (money paid out), with automatic balance updates and transaction numbering.

## Transaction Types

### Inflows (Money In)

- Donor contributions
- Grant disbursements
- Investment income
- Other revenue
- Refunds received

### Outflows (Money Out)

- Expense payments
- Vendor payments
- Salaries and wages
- Operating costs
- Other expenses

## Features

### Automatic Transaction Numbering

Every transaction receives a unique transaction number in the format:

- **Format**: TXN-YYYY-NNNN
- **Example**: TXN-2025-0001
- **Numbering**: Sequential per year, resets annually

### Balance Management

- Real-time balance updates
- Validation to prevent negative balances
- Automatic calculation based on transaction type
- Balance history tracking

### Reconciliation

- Mark transactions as reconciled
- Track reconciliation status
- Filter by reconciliation state
- Reconciliation date tracking

### Project Linking

- Link transactions to specific projects
- Track project-specific cash flows
- Project cash flow reporting

## API Endpoints

### List Cash Flows

```http
GET /api/v1/cash-flows
```

**Query Parameters:**

- `type` (string): Filter by type (inflow/outflow)
- `bank_account_id` (integer): Filter by bank account
- `project_id` (integer): Filter by project
- `is_reconciled` (boolean): Filter by reconciliation status
- `start_date` (date): Filter from date (YYYY-MM-DD)
- `end_date` (date): Filter to date (YYYY-MM-DD)
- `search` (string): Search transaction number or description
- `per_page` (integer): Items per page (default: 15)

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "transaction_number": "TXN-2025-0001",
      "bank_account_id": 1,
      "project_id": 5,
      "type": "inflow",
      "amount": "50000.00",
      "description": "Q4 donor contribution",
      "transaction_date": "2025-11-01",
      "is_reconciled": true,
      "reconciled_at": "2025-11-10T15:30:00.000000Z",
      "reference_number": "DON-2025-Q4-001",
      "notes": "Regular quarterly donation",
      "created_at": "2025-11-01T10:00:00.000000Z",
      "bank_account": {
        "id": 1,
        "account_name": "Main Operations Account",
        "currency": "ZMW"
      },
      "project": {
        "id": 5,
        "project_code": "PRJ-2025-0005",
        "name": "Community Health Initiative"
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Record Inflow

```http
POST /api/v1/cash-flows/inflow
```

**Request Body:**

```json
{
    "bank_account_id": 1,
    "project_id": 5,
    "amount": 50000.0,
    "description": "Q4 donor contribution",
    "transaction_date": "2025-11-01",
    "reference_number": "DON-2025-Q4-001",
    "notes": "Regular quarterly donation"
}
```

**Validation Rules:**

- `bank_account_id`: Required, must exist and be active
- `project_id`: Optional, must exist if provided
- `amount`: Required, numeric, greater than 0
- `description`: Required, max 500 characters
- `transaction_date`: Required, valid date
- `reference_number`: Optional, max 255 characters
- `notes`: Optional, text

**Response (201 Created):**

```json
{
  "message": "Cash inflow recorded successfully",
  "transaction": {
    "id": 1,
    "transaction_number": "TXN-2025-0001",
    ...
  }
}
```

**Business Logic:**

1. Validates bank account is active
2. Generates transaction number (TXN-YYYY-NNNN)
3. Records inflow transaction
4. Updates bank account balance (+amount)
5. Links to project if provided
6. Returns transaction details

### Record Outflow

```http
POST /api/v1/cash-flows/outflow
```

**Request Body:**

```json
{
    "bank_account_id": 1,
    "project_id": 5,
    "amount": 15000.0,
    "description": "Office supplies purchase",
    "transaction_date": "2025-11-05",
    "reference_number": "PO-2025-0123",
    "notes": "Monthly supplies order"
}
```

**Validation Rules:** Same as inflow

**Response (201 Created):**

```json
{
  "message": "Cash outflow recorded successfully",
  "transaction": {
    "id": 2,
    "transaction_number": "TXN-2025-0002",
    ...
  }
}
```

**Business Logic:**

1. Validates bank account is active
2. **Validates sufficient balance** (current_balance >= amount)
3. Generates transaction number
4. Records outflow transaction
5. Updates bank account balance (-amount)
6. Links to project if provided
7. Returns transaction details

**Error Response (422) - Insufficient Balance:**

```json
{
    "message": "Insufficient balance in bank account"
}
```

### View Cash Flow

```http
GET /api/v1/cash-flows/{id}
```

**Response:**

```json
{
  "transaction": {
    "id": 1,
    "transaction_number": "TXN-2025-0001",
    "bank_account_id": 1,
    "project_id": 5,
    "type": "inflow",
    "amount": "50000.00",
    "description": "Q4 donor contribution",
    "transaction_date": "2025-11-01",
    "is_reconciled": true,
    "reconciled_at": "2025-11-10T15:30:00.000000Z",
    "reference_number": "DON-2025-Q4-001",
    "notes": "Regular quarterly donation",
    "bank_account": {...},
    "project": {...}
  }
}
```

### Update Cash Flow

```http
PUT /api/v1/cash-flows/{id}
```

**Request Body:**

```json
{
    "description": "Updated description",
    "reference_number": "NEW-REF-123",
    "notes": "Additional notes"
}
```

**Note:** Cannot update amount, type, bank_account_id, or transaction_date after creation. Only description, reference, and notes can be updated.

### Reconcile Transaction

```http
POST /api/v1/cash-flows/{id}/reconcile
```

**Response:**

```json
{
  "message": "Transaction reconciled successfully",
  "transaction": {
    "id": 1,
    "is_reconciled": true,
    "reconciled_at": "2025-11-15T10:30:00.000000Z",
    ...
  }
}
```

**Business Logic:**

1. Marks transaction as reconciled
2. Records reconciliation timestamp
3. Cannot un-reconcile once reconciled

### Delete Cash Flow

```http
DELETE /api/v1/cash-flows/{id}
```

**Response:**

```json
{
    "message": "Cash flow deleted successfully"
}
```

**Note:** Uses soft deletes - transaction is marked as deleted but remains in database.

## Statistics & Reporting

### Cash Flow Statistics

```http
GET /api/v1/cash-flows/statistics
```

**Query Parameters:**

- `start_date` (date): Filter from date
- `end_date` (date): Filter to date
- `bank_account_id` (integer): Filter by account
- `project_id` (integer): Filter by project

**Response:**

```json
{
  "total_inflows": "500000.00",
  "total_outflows": "350000.00",
  "net_cash_flow": "150000.00",
  "reconciled_count": 45,
  "unreconciled_count": 12,
  "recent_transactions": [...]
}
```

## Business Rules

### Balance Validation

- **Inflows**: No balance check required
- **Outflows**: Must have sufficient balance
- System prevents negative balances
- Validates before recording transaction

### Transaction Numbering

- Format: TXN-YYYY-NNNN
- Sequential per year
- Automatically generated
- Cannot be manually set
- Unique across all transactions

### Reconciliation Rules

- Only Finance Officers can reconcile
- Once reconciled, cannot be un-reconciled
- Reconciliation marks transaction as verified
- Reconciliation date is recorded

### Update Restrictions

- Cannot change amount after creation
- Cannot change type (inflow/outflow) after creation
- Cannot change bank account after creation
- Cannot change transaction date after creation
- Can update description, reference, and notes

### Deletion

- Soft delete only (data retained)
- Finance Officers can delete
- Deleted transactions don't affect current balance
- Can be restored if needed

## User Interface

### Transaction List

- Paginated list with filters
- Color-coded by type (green=inflow, red=outflow)
- Reconciliation status indicator
- Quick search by transaction number
- Date range filters

### Record Transaction Form

- Separate forms for inflow/outflow
- Bank account dropdown (active accounts only)
- Project dropdown (optional)
- Amount validation
- Date picker for transaction date
- Reference number field
- Notes textarea

### Transaction Detail View

- All transaction information
- Bank account details
- Project information (if linked)
- Reconciliation status
- Edit/Delete buttons (if permitted)
- Reconcile button (if not reconciled)

## Best Practices

1. **Regular Entry**: Record transactions daily for accuracy
2. **Clear Descriptions**: Use descriptive text for easy identification
3. **Reference Numbers**: Always include external reference numbers
4. **Project Linking**: Link to projects when applicable
5. **Monthly Reconciliation**: Reconcile all transactions monthly
6. **Date Accuracy**: Use actual transaction date, not entry date

## Common Use Cases

### Recording a Donor Contribution

1. Navigate to Cash Flows
2. Click "Record Inflow"
3. Select bank account
4. Select related project
5. Enter amount and description
6. Add donor reference number
7. Save transaction

### Recording an Expense Payment

1. Navigate to Cash Flows
2. Click "Record Outflow"
3. Select bank account
4. Enter amount (validated against balance)
5. Add expense description
6. Link to project if applicable
7. Save transaction

### Monthly Reconciliation

1. Export bank statement
2. Filter transactions by date range
3. Compare with bank statement
4. Mark matching transactions as reconciled
5. Investigate discrepancies
6. Document reconciliation

## Troubleshooting

**Problem**: Cannot record outflow - "Insufficient balance"
**Solution**: Check bank account balance. Outflows cannot exceed available balance.

**Problem**: Transaction number not generated
**Solution**: System auto-generates transaction numbers. Do not enter manually.

**Problem**: Cannot update transaction amount
**Solution**: Amounts cannot be changed after creation. Delete and re-create if needed.

**Problem**: Cannot find transaction
**Solution**: Use search by transaction number or description. Check date filters.
