# Expense Management Module - Technical Documentation

## Architecture Overview

The Expense Management module follows Laravel's MVC architecture with a service-oriented approach, implementing a three-tier approval workflow with role-based access control.

### Technology Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Vue.js 3, Pinia, Tailwind CSS 4
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **Notifications**: Laravel Notifications (Mail)
- **File Storage**: Laravel Filesystem (public disk)

## Database Schema

### Tables

#### expenses

Primary table storing expense records.

```sql
CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_number VARCHAR(50) UNIQUE NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    budget_item_id BIGINT UNSIGNED NOT NULL,
    expense_category_id BIGINT UNSIGNED NOT NULL,
    expense_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT NOT NULL,
    receipt_path VARCHAR(255) NULL,
    status ENUM('Draft', 'Submitted', 'Under Review', 'Reviewed', 'Approved', 'Rejected', 'Paid') DEFAULT 'Draft',
    submitted_by BIGINT UNSIGNED NOT NULL,
    submitted_at TIMESTAMP NULL,
    payment_date DATE NULL,
    payment_reference VARCHAR(100) NULL,
    payment_method VARCHAR(50) NULL,
    payment_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_item_id) REFERENCES budget_items(id) ON DELETE RESTRICT,
    FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_project (project_id),
    INDEX idx_submitted_by (submitted_by)
);
```

#### expense_categories

Categorization system for expenses.

```sql
CREATE TABLE expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### expense_approvals

Audit trail for approval workflow.

```sql
CREATE TABLE expense_approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_id BIGINT UNSIGNED NOT NULL,
    approver_id BIGINT UNSIGNED NOT NULL,
    stage ENUM('review', 'approval') NOT NULL,
    action ENUM('approve', 'reject') NOT NULL,
    comments TEXT NULL,
    approved_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_expense (expense_id),
    INDEX idx_approver (approver_id)
);
```

### Relationships

- **Expense** belongsTo **Project**
- **Expense** belongsTo **BudgetItem**
- **Expense** belongsTo **ExpenseCategory**
- **Expense** belongsTo **User** (submitter)
- **Expense** hasMany **ExpenseApprovals**
- **ExpenseApproval** belongsTo **Expense**
- **ExpenseApproval** belongsTo **User** (approver)

## Backend Architecture

### Models

#### Expense Model

Location: `app/Models/Expense.php`

```php
class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number', 'project_id', 'budget_item_id',
        'expense_category_id', 'expense_date', 'amount',
        'description', 'receipt_path', 'status', 'submitted_by',
        'submitted_at', 'payment_date', 'payment_reference',
        'payment_method', 'payment_notes'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'payment_date' => 'date'
    ];

    // Relationships
    public function project(): BelongsTo
    public function budgetItem(): BelongsTo
    public function category(): BelongsTo
    public function submitter(): BelongsTo
    public function approvals(): HasMany

    // Scopes
    public function scopeDraft($query)
    public function scopeSubmitted($query)
    public function scopePendingReview($query)
    public function scopePendingApproval($query)
    public function scopeApproved($query)
    public function scopePaid($query)
}
```

### Services

#### ExpenseService

Location: `app/Services/ExpenseService.php`

Handles business logic for expense operations.

**Key Methods:**

- `createExpense(array $data)`: Create new expense with auto-generated number
- `updateExpense(Expense $expense, array $data)`: Update draft expense
- `submitExpense(Expense $expense)`: Submit for review, notify Finance Officers
- `reviewExpense(Expense $expense, string $action, ?string $comments)`: Finance review
- `approveExpense(Expense $expense, string $action, ?string $comments)`: Programs Manager approval
- `markAsPaid(Expense $expense, array $paymentData)`: Process payment
- `recordExpense(Expense $expense)`: Update budget tracking

#### ApprovalService

Location: `app/Services/ApprovalService.php`

Manages approval workflow and audit trail.

**Key Methods:**

- `recordApproval(Expense $expense, User $approver, string $stage, string $action, ?string $comments)`: Log approval action

#### FileUploadService

Location: `app/Services/FileUploadService.php`

Handles file uploads and validation.

**Key Methods:**

- `uploadReceipt(UploadedFile $file)`: Upload receipt, return path
- `deleteReceipt(?string $path)`: Remove receipt file
- `validateFile(UploadedFile $file)`: Validate file type and size

### Controllers

#### ExpenseController

Location: `app/Http/Controllers/Api/ExpenseController.php`

RESTful API controller with 12 endpoints.

**Endpoints:**

```php
GET    /api/v1/expenses                  # List expenses (paginated, filtered)
POST   /api/v1/expenses                  # Create expense
GET    /api/v1/expenses/{id}             # View expense details
PUT    /api/v1/expenses/{id}             # Update draft expense
DELETE /api/v1/expenses/{id}             # Delete draft expense
POST   /api/v1/expenses/{id}/submit      # Submit for review
POST   /api/v1/expenses/{id}/review      # Finance review
POST   /api/v1/expenses/{id}/approve     # Programs Manager approval
POST   /api/v1/expenses/{id}/mark-paid   # Mark as paid
GET    /api/v1/expenses/categories       # List categories
GET    /api/v1/expenses/pending-review   # Finance pending list
GET    /api/v1/expenses/pending-approval # PM pending list
```

### Policies

#### ExpensePolicy

Location: `app/Policies/ExpensePolicy.php`

Defines authorization rules.

**Methods:**

- `viewAny(User $user)`: Can view expense list
- `view(User $user, Expense $expense)`: Can view specific expense
- `create(User $user)`: Can create expense (Project Officers)
- `update(User $user, Expense $expense)`: Can update (own draft only)
- `delete(User $user, Expense $expense)`: Can delete (own draft + PM)
- `submit(User $user, Expense $expense)`: Can submit (own draft)
- `review(User $user, Expense $expense)`: Can review (Finance Officers)
- `approve(User $user, Expense $expense)`: Can approve (Programs Managers)
- `markAsPaid(User $user, Expense $expense)`: Can mark paid (Finance Officers)

### Form Requests

#### StoreExpenseRequest

Validates expense creation.

**Rules:**

```php
[
    'project_id' => 'required|exists:projects,id',
    'budget_item_id' => 'required|exists:budget_items,id',
    'expense_category_id' => 'required|exists:expense_categories,id',
    'expense_date' => 'required|date',
    'amount' => 'required|numeric|min:0.01',
    'description' => 'required|string|max:1000',
    'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
]
```

#### ReviewExpenseRequest

Validates review/approval actions.

**Rules:**

```php
[
    'action' => 'required|in:approve,reject',
    'comments' => 'nullable|string|max:1000|required_if:action,reject'
]
```

### Notifications

#### ExpenseSubmittedNotification

Sent to Finance Officers when expense submitted.

#### ExpenseReviewedNotification

Sent to Programs Managers when expense reviewed.

#### ExpenseApprovedNotification

Sent to Project Officer when expense approved.

#### ExpenseRejectedNotification

Sent to Project Officer when expense rejected.

#### ExpensePaidNotification

Sent to Project Officer when expense paid.

## Frontend Architecture

### Vue Components

#### ExpensesList.vue

Main listing page with search, filters, pagination.

**Features:**

- Filterable table (status, project, category)
- Search by expense number/description
- Inline actions (view, edit, delete)
- Role-based button display
- Pagination controls

#### CreateExpense.vue

Form for creating/editing expenses.

**Features:**

- Auto-loading project budget items
- File upload with drag-and-drop
- Dual save options (draft / submit)
- Client-side validation
- Edit mode support (route param detection)

#### ViewExpense.vue

Detailed expense view with actions.

**Features:**

- Complete expense information
- Approval timeline visualization
- Receipt download link
- Payment information (if paid)
- Role-based action buttons
- Review/approval modals
- Payment processing modal

#### PendingReview.vue

Finance Officer dashboard for pending reviews.

**Features:**

- Stats card with total pending count/amount
- Quick review actions
- Inline approve/reject
- Modal for detailed review
- Real-time badge updates

#### PendingApproval.vue

Programs Manager dashboard for pending approvals.

**Features:**

- Stats card with total pending count/amount
- Finance review comments display
- Quick approval actions
- Inline approve/reject
- Modal for detailed approval
- Real-time badge updates

### Pinia Store

#### expenseStore.js

Location: `resources/js/stores/expenseStore.js`

**State:**

```javascript
{
    expenses: [],
    categories: [],
    currentExpense: null,
    pendingReview: [],
    pendingApproval: [],
    loading: false,
    error: null,
    pagination: { current_page, per_page, total, last_page },
    filters: { status, project_id, expense_category_id, date_from, date_to, search }
}
```

**Getters:**

- `pendingCount`: Badge count based on user role
- `myExpenses`: Current user's expenses
- `filteredExpenses`: Apply client-side filters

**Actions:**

- `fetchExpenses(page)`: Load paginated expenses
- `fetchCategories()`: Load categories
- `fetchPendingReview()`: Load Finance pending
- `fetchPendingApproval()`: Load PM pending
- `createExpense(data)`: Create with file upload
- `updateExpense(id, data)`: Update with file upload
- `submitExpense(id)`: Submit for review
- `reviewExpense(id, action, comments)`: Finance review
- `approveExpense(id, action, comments)`: PM approval
- `markAsPaid(id, paymentData)`: Process payment

## Testing

### Test Structure

Location: `tests/Feature/Expenses/ExpenseManagementTest.php`

**Test Coverage (100% - 10/10 passing):**

1. ✅ `project_officer_can_create_expense`
2. ✅ `project_officer_can_submit_expense`
3. ✅ `finance_officer_can_review_expense`
4. ✅ `programs_manager_can_approve_expense`
5. ✅ `finance_officer_can_mark_as_paid`
6. ✅ `project_officer_cannot_edit_submitted_expense`
7. ✅ `project_officer_can_only_view_own_expenses`
8. ✅ `expense_can_be_rejected_at_any_stage`
9. ✅ `expense_can_include_receipt_upload`
10. ✅ `complete_approval_workflow`

**Running Tests:**

```bash
# All expense tests
php artisan test --filter=ExpenseManagementTest

# Specific test
php artisan test --filter=expense_can_include_receipt_upload

# With coverage
php artisan test --filter=ExpenseManagementTest --coverage
```

## API Reference

### Authentication

All endpoints require Sanctum authentication.

```javascript
headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
}
```

### Response Format

**Success:**

```json
{
    "message": "Success message",
    "expense": {
        /* expense object */
    }
}
```

**Error:**

```json
{
    "message": "Error message",
    "errors": {
        "field": ["Validation error"]
    }
}
```

### Pagination

```json
{
    "data": [],
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
}
```

## Security Considerations

### Authorization

- All actions validated through ExpensePolicy
- Role-based access control enforced
- Own resource checks (Project Officers)
- Status-based permission checks

### File Uploads

- MIME type validation (PDF, JPG, JPEG, PNG)
- File size limit (5MB)
- Unique filename generation (UUID)
- Storage in non-public-accessible directory
- Secure file serving through controller

### Data Validation

- Server-side validation on all inputs
- SQL injection prevention (Eloquent ORM)
- XSS protection (Vue escaping)
- CSRF protection (Sanctum)

### Audit Trail

- Complete approval history
- Soft deletes for data retention
- Timestamps on all records
- User tracking for all actions

## Performance Optimization

### Database

- Indexed columns (status, project_id, submitted_by)
- Eager loading relationships
- Query optimization (N+1 prevention)
- Pagination for large datasets

### Caching Strategy

- Categories cached (rarely change)
- Pending counts cached per user role
- Cache invalidation on status changes

### Frontend

- Lazy loading components
- Debounced search inputs
- Optimistic UI updates
- Chunked file uploads

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed expense categories: `php artisan db:seed --class=ExpenseCategorySeeder`
- [ ] Configure storage link: `php artisan storage:link`
- [ ] Set file permissions (storage/app/public)
- [ ] Build frontend assets: `npm run build`
- [ ] Configure mail settings (.env)
- [ ] Test notification delivery
- [ ] Verify file upload works
- [ ] Run test suite: `php artisan test`
- [ ] Clear caches: `php artisan optimize:clear`

## Troubleshooting

### Common Issues

**500 Error on Submit**

- Check notification configuration
- Verify SMTP settings
- Check expense_approvals table exists
- Review logs: `storage/logs/laravel.log`

**File Upload Fails**

- Verify storage link exists
- Check disk permissions (775 for storage/)
- Confirm max upload size (php.ini)
- Check available disk space

**Badge Count Wrong**

- Clear cache: `php artisan cache:clear`
- Verify role slugs match ('finance-officer', not 'Finance Officer')
- Check pending counts in database directly

**Notifications Not Sending**

- Test mail config: `php artisan tinker` → `Mail::raw('test', fn($m)=>$m->to('email'))`
- Check queue if using: `php artisan queue:work`
- Verify recipient email addresses
- Check spam folder

## Future Enhancements

### Planned Features

1. **Batch Processing**: Approve multiple expenses simultaneously
2. **Advanced Reporting**: Generate expense reports by project, category, period
3. **PDF Export**: Download expenses as PDF
4. **OCR Integration**: Auto-extract data from receipt images
5. **Mobile App**: Native mobile expense submission
6. **Recurring Expenses**: Template-based recurring expenses
7. **Multi-currency**: Support for foreign currency expenses
8. **Approval Delegation**: Temporary approval authority delegation
9. **Expense Templates**: Pre-fill common expense types
10. **Analytics Dashboard**: Visualize expense trends and patterns

---

**Module Version**: 1.0.0  
**API Version**: v1  
**Last Updated**: November 15, 2025  
**Maintained By**: Development Team, CAN-Zimbabwe
