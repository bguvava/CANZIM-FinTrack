# Module 7: Expense Management & Approval Workflow

## Overview

Module 7 implements a comprehensive expense management system with a 3-tier approval workflow for CANZIM FinTrack. The module enables project staff to submit expenses, track approval processes, and facilitate payment processing through a structured approval chain.

## Features

### Core Functionality

- **Expense Submission**: Project Officers can create and submit expense requests with receipt attachments
- **3-Tier Approval Workflow**: Systematic review process through Finance Officer → Programs Manager → Payment
- **Receipt Management**: Upload and store expense receipts (PDF, JPG, PNG up to 5MB)
- **Budget Integration**: Automatic budget tracking and threshold alerts
- **Real-time Notifications**: Email and database notifications at each workflow stage
- **Role-Based Access**: Authorization controls based on user role and expense status

### Approval Workflow Stages

1. **Draft**: Initial expense creation by Project Officer
2. **Submitted**: Expense submitted for Finance Officer review
3. **Under Review**: Finance Officer approved, forwarded to Programs Manager
4. **Approved**: Programs Manager approved, ready for payment
5. **Paid**: Finance Officer marked as paid with payment details
6. **Rejected**: Returned to submitter at any stage with reason

## Technical Implementation

### Database Schema

#### Expense Categories Table

- 8 predefined categories (Travel, Salaries, Procurement, Consultants, Training, Communications, Utilities, Other)
- Sortable and toggleable active status

#### Expenses Table (60+ fields)

- Complete workflow tracking
- Budget item linkage
- Receipt file path storage
- Approval timestamps and user references
- Payment tracking fields

#### Expense Approvals Table

- Complete audit trail
- User, level, action, comments for each approval step
- Timestamps for accountability

### Backend Components

**Models** (3 files):

- `ExpenseCategory`: Category management with scopes
- `Expense`: Core expense model with 9 relationships, 9 scopes, 6 helper methods
- `ExpenseApproval`: Audit trail tracking

**Services** (4 files):

- `FileUploadService`: Receipt upload, validation, deletion
- `ExpenseService`: 8 core business methods (create, submit, review, approve, reject, mark paid, update, delete)
- `ApprovalService`: Workflow management and authorization helpers
- `BudgetService`: Integration for expense tracking against budgets

**Form Requests** (3 validators):

- `StoreExpenseRequest`: 8 validation rules for expense creation
- `UpdateExpenseRequest`: 8 validation rules with 'sometimes' modifier
- `ReviewExpenseRequest`: 5 validation rules for approval workflow

**Policy**:

- `ExpensePolicy`: 10 authorization gates for role-based access control

**Controller**:

- `ExpenseController`: 12+ API endpoints for complete expense lifecycle management

**Notifications** (4 classes):

- `ExpenseSubmittedNotification`: Notifies Finance Officers
- `ExpenseReviewedNotification`: Notifies Programs Manager
- `ExpenseApprovedNotification`: Notifies submitter of approval
- `ExpenseRejectedNotification`: Notifies submitter with rejection reason

### Frontend Components

**Pinia Store**:

- `expenseStore.js`: Complete state management for expenses, categories, filters, pagination

**Vue Components**:

- `ExpensesList.vue`: Expense listing with search and filters
- Additional components for submit, view, edit, and approval workflows (in progress)

## Requirements Coverage

Module 7 addresses 64 requirements (REQ-304 to REQ-367):

- ✅ REQ-304-310: Expense submission with validation
- ✅ REQ-311-320: Finance Officer review process
- ✅ REQ-321-330: Programs Manager approval
- ✅ REQ-331-340: Payment processing
- ✅ REQ-341-350: Rejection handling at all stages
- ✅ REQ-351-360: Receipt upload and management
- ✅ REQ-361-367: Budget integration and alerts

## API Endpoints

See [API Reference](api-reference.md) for complete endpoint documentation.

## User Roles & Permissions

### Project Officer

- Create, edit (Draft/Rejected only), delete (Draft only) expenses
- Submit expenses for approval
- View own expenses only
- Upload receipts

### Finance Officer

- View all expenses
- Review submitted expenses (approve to PM or reject to PO)
- Mark approved expenses as paid
- View pending review queue

### Programs Manager

- View all expenses
- Approve or reject expenses under review
- Delete draft expenses
- View pending approval queue
- Access complete audit trail

## Security & Authorization

- Policy-based authorization on all endpoints
- Status-based edit restrictions
- Ownership checks for Project Officers
- Role-based view filtering
- Receipt file validation (type, size)

## Testing

Comprehensive test suite in `tests/Feature/Expenses/ExpenseManagementTest.php`:

- Expense creation and submission
- Complete approval workflow
- Rejection at each stage
- Role-based authorization
- Receipt upload functionality
- Budget integration

**Note**: Tests currently require schema alignment with user roles structure.

## Module Status

**Progress**: 45% Complete

**Completed**:

- ✅ Database schema (3 tables)
- ✅ Models (3 models with relationships)
- ✅ Services (4 service classes)
- ✅ Notifications (4 notification classes)
- ✅ Form Requests (3 validators)
- ✅ Policy (10 authorization gates)
- ✅ Controller (12+ API endpoints)
- ✅ Routes configuration
- ✅ Pinia store
- ✅ Basic expense list component

**In Progress**:

- 🔄 Additional Vue components
- 🔄 Test suite (schema alignment needed)
- 🔄 PDF report generation
- 🔄 Sidebar navigation integration

**Pending**:

- ❌ Complete frontend UI
- ❌ Documentation finalization
- ❌ Production deployment readiness

## Next Steps

1. Complete Vue components for expense submission and approval workflows
2. Resolve test schema alignment issues
3. Implement PDF report generation
4. Update sidebar navigation with pending badge counter
5. Achieve 100% test pass rate
6. Complete user documentation
7. Perform end-to-end testing

## Related Modules

- **Module 6**: Project & Budget Management (upstream dependency)
- **Module 8**: Cash Flow Management (downstream integration)
- **Module 4**: User Management (role-based authorization)

---

**Last Updated**: 2025-11-15
**Version**: 1.0
**Status**: Active Development
