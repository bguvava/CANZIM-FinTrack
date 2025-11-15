# Bank Account Management

## Overview

Bank Account Management allows Finance Officers to create and maintain multiple bank accounts for the organization. Each account tracks its own balance, currency, and transaction history.

## Features

### Account Creation

Finance Officers can create new bank accounts with the following information:

- **Account Name**: Descriptive name (e.g., "Main Operations Account")
- **Account Number**: Bank account number
- **Bank Name**: Name of the banking institution
- **Branch**: Bank branch location (optional)
- **Currency**: Account currency (USD, EUR, GBP, ZMW, etc.)
- **Initial Balance**: Starting balance when creating the account

### Account Management

#### Activation/Deactivation

- Accounts can be deactivated when no longer in use
- Deactivated accounts cannot receive new transactions
- Existing transactions remain accessible
- Accounts can be reactivated at any time

#### Account Summary

Each account provides a comprehensive summary:

- Current balance
- Total inflows (all-time)
- Total outflows (all-time)
- Recent 10 transactions
- Account status (active/inactive)

## API Endpoints

### List Bank Accounts

```http
GET /api/v1/bank-accounts
```

**Query Parameters:**

- `is_active` (boolean): Filter by active status
- `currency` (string): Filter by currency code
- `search` (string): Search account name, number, or bank name
- `per_page` (integer): Items per page (default: 15)

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "account_name": "Main Operations Account",
      "account_number": "1234567890",
      "bank_name": "Standard Bank",
      "branch": "Lusaka Central",
      "currency": "ZMW",
      "current_balance": "150000.00",
      "is_active": true,
      "created_at": "2025-01-15T10:00:00.000000Z",
      "updated_at": "2025-11-15T14:30:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create Bank Account

```http
POST /api/v1/bank-accounts
```

**Request Body:**

```json
{
    "account_name": "Main Operations Account",
    "account_number": "1234567890",
    "bank_name": "Standard Bank",
    "branch": "Lusaka Central",
    "currency": "ZMW",
    "current_balance": 150000.0
}
```

**Validation Rules:**

- `account_name`: Required, max 255 characters
- `account_number`: Required, unique, max 100 characters
- `bank_name`: Required, max 255 characters
- `branch`: Optional, max 255 characters
- `currency`: Required, valid currency code (USD, EUR, GBP, ZMW, etc.)
- `current_balance`: Required, numeric, minimum 0

**Response (201 Created):**

```json
{
  "message": "Bank account created successfully",
  "account": {
    "id": 1,
    "account_name": "Main Operations Account",
    ...
  }
}
```

### View Bank Account

```http
GET /api/v1/bank-accounts/{id}
```

**Response:**

```json
{
    "account": {
        "id": 1,
        "account_name": "Main Operations Account",
        "account_number": "1234567890",
        "bank_name": "Standard Bank",
        "branch": "Lusaka Central",
        "currency": "ZMW",
        "current_balance": "150000.00",
        "is_active": true,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-11-15T14:30:00.000000Z"
    }
}
```

### Update Bank Account

```http
PUT /api/v1/bank-accounts/{id}
```

**Request Body:**

```json
{
    "account_name": "Updated Account Name",
    "bank_name": "New Bank",
    "branch": "New Branch"
}
```

**Note:** `account_number`, `currency`, and `current_balance` cannot be updated through this endpoint. Balance updates happen through cash flow transactions.

### Deactivate Account

```http
POST /api/v1/bank-accounts/{id}/deactivate
```

**Response:**

```json
{
  "message": "Bank account deactivated successfully",
  "account": {...}
}
```

### Activate Account

```http
POST /api/v1/bank-accounts/{id}/activate
```

**Response:**

```json
{
  "message": "Bank account activated successfully",
  "account": {...}
}
```

### Account Summary

```http
GET /api/v1/bank-accounts/{id}/summary
```

**Response:**

```json
{
    "account": {
        "id": 1,
        "account_name": "Main Operations Account",
        "account_number": "1234567890",
        "current_balance": "150000.00"
    },
    "total_inflows": "500000.00",
    "total_outflows": "350000.00",
    "recent_transactions": [
        {
            "id": 45,
            "transaction_number": "TXN-2025-0045",
            "type": "inflow",
            "amount": "25000.00",
            "description": "Donor contribution",
            "transaction_date": "2025-11-10T00:00:00.000000Z"
        }
    ]
}
```

## Business Rules

### Account Number Uniqueness

- Each account number must be unique across all bank accounts
- System validates uniqueness before creating account
- Prevents duplicate account registration

### Balance Updates

- Balances are automatically updated when cash flows are recorded
- Inflows increase the balance
- Outflows decrease the balance
- Manual balance adjustments are not allowed

### Deactivation Rules

- Only Finance Officers can deactivate accounts
- Deactivated accounts cannot receive new transactions
- Historical data remains accessible
- Deactivation does not affect existing transactions

### Currency Restrictions

- Once set, account currency cannot be changed
- All transactions must use the account's currency
- Multi-currency transactions require exchange rate handling

## User Interface

### Account List View

- Paginated list of all bank accounts
- Filter by active status
- Filter by currency
- Search by account name, number, or bank name
- Sort by various columns

### Account Detail View

- Complete account information
- Current balance prominently displayed
- Recent transaction list
- Quick actions (deactivate, edit, view summary)

### Create/Edit Form

- Validation on all fields
- Currency dropdown with common options
- Balance formatted with 2 decimal places
- Branch as optional field

## Best Practices

1. **Account Naming**: Use clear, descriptive names (e.g., "Payroll Account - ZMW")
2. **Regular Reconciliation**: Reconcile accounts monthly against bank statements
3. **Deactivation**: Deactivate unused accounts to keep the list manageable
4. **Currency Codes**: Use standard ISO 4217 currency codes
5. **Documentation**: Add notes about account purpose in the account name

## Common Use Cases

### Setting Up a New Bank Account

1. Navigate to Bank Accounts
2. Click "Add New Account"
3. Enter account details
4. Set initial balance
5. Save account

### Finding an Account

1. Use search box to find by name, number, or bank
2. Or filter by currency
3. Or filter by active/inactive status

### Viewing Account Activity

1. Click on account in list
2. View summary section
3. See recent transactions
4. Access full transaction history via Cash Flows

## Troubleshooting

**Problem**: Cannot create account - "Account number already exists"
**Solution**: Check if account number is already in the system. Each account number must be unique.

**Problem**: Cannot deactivate account
**Solution**: Ensure you have Finance Officer role. Only Finance Officers can deactivate accounts.

**Problem**: Balance doesn't match bank statement
**Solution**: Review all transactions and reconcile. The balance is calculated from recorded transactions.
