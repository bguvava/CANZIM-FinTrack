# Cash Flow Management Module

## Overview

The Cash Flow Management module provides comprehensive financial tracking capabilities for CANZIM FinTrack. It enables organizations to monitor cash inflows and outflows across multiple bank accounts, reconcile transactions, and generate cash flow projections.

## Module Components

### 1. Bank Account Management

- Multi-currency bank account tracking
- Account activation/deactivation
- Real-time balance monitoring
- Transaction history

### 2. Cash Flow Tracking

- Record cash inflows (donations, grants, income)
- Record cash outflows (expenses, payments)
- Automatic transaction numbering (TXN-YYYY-NNNN)
- Balance validation and updates

### 3. Cash Flow Reconciliation

- Mark transactions as reconciled
- Track reconciliation status
- Filter by reconciliation state

### 4. Cash Flow Projections

- 3-12 month cash flow forecasting
- Based on 6-month historical averages
- Monthly breakdown of projected inflows/outflows
- Projected account balances

## Key Features

### Multi-Currency Support

- Track accounts in different currencies (USD, EUR, GBP, ZMW, etc.)
- Currency-specific balance calculations
- Filter and search by currency

### Auto-Numbering

- **Transaction Numbers**: TXN-YYYY-NNNN (e.g., TXN-2025-0001)
- **Bank Account Codes**: Auto-generated unique identifiers
- Sequential numbering per year

### Balance Management

- Real-time balance updates on transactions
- Validation to prevent negative balances
- Automatic balance recalculation

### Reporting & Analytics

- Cash flow statistics dashboard
- Total inflows/outflows by period
- Reconciled vs unreconciled tracking
- Recent transaction summaries

## User Roles & Permissions

### Finance Officer

- Create and manage bank accounts
- Record cash inflows and outflows
- Reconcile transactions
- View cash flow reports and projections
- Deactivate/activate accounts

### Programs Manager

- View all bank accounts
- View cash flow transactions
- Access reports and projections
- Approve major transactions (if required)

### Executive Director

- View-only access to all cash flow data
- Access to reports and projections

### Project Officer

- View cash flows related to their projects
- Limited access to financial details

## Integration Points

### Projects Module

- Link cash flows to specific projects
- Track project-specific cash movements
- Project-level cash flow reporting

### Expense Management

- Link outflows to approved expenses
- Automatic expense payment recording
- Expense-to-cash flow reconciliation

### Budget Module

- Compare actual cash flows vs budgeted amounts
- Budget variance analysis
- Cash flow impact on budget utilization

## Technical Details

### Database Tables

- `bank_accounts` - Bank account master data
- `cash_flows` - All cash flow transactions

### API Endpoints

See individual documentation files for detailed API information:

- [Bank Account API](BankAccountAPI.md)
- [Cash Flow API](CashFlowAPI.md)

### Business Rules

See [Business Rules](BusinessRules.md) for detailed validation and processing rules.

## Getting Started

1. **Set up Bank Accounts**: Create bank accounts for your organization
2. **Record Transactions**: Log all cash inflows and outflows
3. **Reconcile Regularly**: Mark transactions as reconciled monthly
4. **Monitor Projections**: Review cash flow forecasts for planning
5. **Generate Reports**: Use statistics for financial reporting

## Related Documentation

- [Bank Account Management](BankAccounts.md)
- [Cash Flow Transactions](CashFlowTransactions.md)
- [Cash Flow Projections](CashFlowProjections.md)
- [API Reference](API.md)

## Support

For technical support or questions about the Cash Flow module, contact the CANZIM FinTrack support team.
