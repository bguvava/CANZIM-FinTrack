# CANZIM FinTrack - Entity Relationship Diagram (ERD)

**Version:** 1.0.0  
**Last Updated:** November 14, 2025  
**Database:** my_canzimdb  
**Module:** Database Design & Migrations

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Core Entities](#core-entities)
3. [Financial Entities](#financial-entities)
4. [System Entities](#system-entities)
5. [Relationships Summary](#relationships-summary)
6. [ERD Diagram](#erd-diagram)

---

## Overview

The CANZIM FinTrack database schema is designed to support a comprehensive Financial Management and Accounting ERP system with integrated project tracking capabilities. The schema follows normalization principles (3NF) and implements proper foreign key constraints for referential integrity.

### Key Design Principles

- **Financial Operations First**: Schema prioritizes financial data integrity and tracking
- **Audit Trail**: Comprehensive tracking of all data changes and user activities
- **Soft Deletes**: Most tables support soft deletion for data recovery
- **Polymorphic Relationships**: Comments and documents can attach to multiple entity types
- **Approval Workflows**: Multi-tier approval tracking for expenses and budgets
- **Performance Optimization**: Strategic indexes on frequently queried columns

---

## Core Entities

### 1. Users Table

**Purpose:** Store user accounts with role-based access control

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Full name |
| email | VARCHAR(255) | Unique email address |
| password | VARCHAR(255) | Hashed password |
| role_id | BIGINT UNSIGNED (FK) | References roles.id |
| office_location | VARCHAR(255) | User's office location |
| status | ENUM | active, inactive, suspended |
| email_verified_at | TIMESTAMP | Email verification timestamp |
| remember_token | VARCHAR(100) | Remember me token |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (role_id)
- INDEX (status)

**Relationships:**
- belongs to Role
- has many Projects (created_by)
- has many Expenses (submitted_by)
- has many Comments
- has many Documents (uploaded_by)

---

### 2. Roles Table

**Purpose:** Define user roles with hierarchical permissions

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Role name (Programs Manager, Finance Officer, Project Officer) |
| slug | VARCHAR(255) | URL-friendly slug |
| description | TEXT | Role description |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name)
- UNIQUE (slug)
- INDEX (slug)

**Relationships:**
- has many Users

---

### 3. Projects Table

**Purpose:** Financial containers for budget tracking and expense allocation

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| code | VARCHAR(255) | Unique project code |
| name | VARCHAR(255) | Project name |
| description | TEXT | Project description |
| start_date | DATE | Project start date |
| end_date | DATE | Project end date |
| total_budget | DECIMAL(15,2) | Total allocated budget |
| status | ENUM | planning, active, on_hold, completed, cancelled |
| office_location | VARCHAR(255) | Project office location |
| created_by | BIGINT UNSIGNED (FK) | References users.id |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (code)
- INDEX (code)
- INDEX (status)
- INDEX (start_date)
- INDEX (end_date)
- INDEX (created_by)

**Relationships:**
- belongs to User (creator)
- has many Budgets
- has many Expenses
- has many PurchaseOrders
- belongs to many Donors (through project_donors)
- has many InKindContributions
- has many CashFlow entries
- morphs many Comments
- morphs many Documents

---

### 4. Donors Table

**Purpose:** Track funding organizations and contributors

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Donor organization name |
| contact_person | VARCHAR(255) | Primary contact person |
| email | VARCHAR(255) | Contact email |
| phone | VARCHAR(255) | Contact phone number |
| address | TEXT | Donor address |
| funding_total | DECIMAL(15,2) | Total funding provided |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (name)
- INDEX (email)

**Relationships:**
- belongs to many Projects (through project_donors)
- has many InKindContributions
- has many CashFlow entries

---

### 5. Project_Donors Table (Pivot)

**Purpose:** Many-to-many relationship between projects and donors with funding details

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| donor_id | BIGINT UNSIGNED (FK) | References donors.id |
| funding_amount | DECIMAL(15,2) | Amount of funding |
| funding_period_start | DATE | Funding period start |
| funding_period_end | DATE | Funding period end |
| is_restricted | BOOLEAN | Whether funding has restrictions |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (donor_id)
- UNIQUE (project_id, donor_id)

**Relationships:**
- belongs to Project
- belongs to Donor

---

## Financial Entities

### 6. Budgets Table

**Purpose:** Store project budget allocations with approval workflow

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| fiscal_year | VARCHAR(255) | Budget fiscal year |
| total_amount | DECIMAL(15,2) | Total budget amount |
| status | ENUM | draft, submitted, approved, rejected |
| approved_by | BIGINT UNSIGNED (FK) | References users.id |
| approved_at | TIMESTAMP | Approval timestamp |
| created_by | BIGINT UNSIGNED (FK) | References users.id |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (fiscal_year)
- INDEX (status)
- COMPOSITE INDEX (project_id, fiscal_year)

**Relationships:**
- belongs to Project
- belongs to User (creator)
- belongs to User (approver)
- has many BudgetItems

---

### 7. Budget_Items Table

**Purpose:** Detailed line items within project budgets

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| budget_id | BIGINT UNSIGNED (FK) | References budgets.id |
| category | VARCHAR(255) | Budget category |
| description | TEXT | Item description |
| cost_code | VARCHAR(255) | Accounting cost code |
| allocated_amount | DECIMAL(15,2) | Allocated budget amount |
| spent_amount | DECIMAL(15,2) | Amount already spent |
| remaining_amount | DECIMAL(15,2) | Remaining budget amount |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (budget_id)
- INDEX (category)
- INDEX (cost_code)

**Relationships:**
- belongs to Budget
- has many Expenses

---

### 8. Expense_Categories Table

**Purpose:** Standardized expense classification

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Category name |
| slug | VARCHAR(255) | URL-friendly slug |
| description | TEXT | Category description |
| is_active | BOOLEAN | Whether category is active |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name)
- UNIQUE (slug)
- INDEX (slug)
- INDEX (is_active)

**Relationships:**
- has many Expenses

**Default Categories:**
- Travel
- Staff Salaries
- Procurement/Supplies
- Consultants/Contractors
- Other

---

### 9. Expenses Table

**Purpose:** Track expense transactions with multi-tier approval workflow

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| budget_item_id | BIGINT UNSIGNED (FK) | References budget_items.id |
| category_id | BIGINT UNSIGNED (FK) | References expense_categories.id |
| expense_date | DATE | Date of expense |
| amount | DECIMAL(15,2) | Expense amount |
| description | TEXT | Expense description |
| receipt_path | VARCHAR(255) | File path to receipt |
| status | ENUM | draft, submitted, under_review, approved, rejected, paid |
| submitted_by | BIGINT UNSIGNED (FK) | References users.id |
| reviewed_by | BIGINT UNSIGNED (FK) | References users.id |
| approved_by | BIGINT UNSIGNED (FK) | References users.id |
| submitted_at | TIMESTAMP | Submission timestamp |
| reviewed_at | TIMESTAMP | Review timestamp |
| approved_at | TIMESTAMP | Approval timestamp |
| rejection_reason | TEXT | Reason for rejection |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (category_id)
- INDEX (expense_date)
- INDEX (status)
- INDEX (submitted_by)
- COMPOSITE INDEX (project_id, status)
- COMPOSITE INDEX (expense_date, status)

**Relationships:**
- belongs to Project
- belongs to BudgetItem
- belongs to ExpenseCategory
- belongs to User (submitter)
- belongs to User (reviewer)
- belongs to User (approver)
- has many CashFlow entries
- morphs many Comments
- morphs many Documents

---

### 10. Vendors Table

**Purpose:** Supplier and vendor information management

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Vendor name |
| contact_person | VARCHAR(255) | Primary contact person |
| email | VARCHAR(255) | Contact email |
| phone | VARCHAR(255) | Contact phone |
| address | TEXT | Vendor address |
| tax_id | VARCHAR(255) | Tax identification number |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (name)
- INDEX (email)

**Relationships:**
- has many PurchaseOrders

---

### 11. Purchase_Orders Table

**Purpose:** Track purchase orders with approval workflow

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| vendor_id | BIGINT UNSIGNED (FK) | References vendors.id |
| po_number | VARCHAR(255) | Unique PO number |
| po_date | DATE | Purchase order date |
| total_amount | DECIMAL(15,2) | Total PO amount |
| status | ENUM | draft, pending, approved, rejected, completed, cancelled |
| description | TEXT | PO description |
| created_by | BIGINT UNSIGNED (FK) | References users.id |
| approved_by | BIGINT UNSIGNED (FK) | References users.id |
| approved_at | TIMESTAMP | Approval timestamp |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (po_number)
- INDEX (po_number)
- INDEX (project_id)
- INDEX (vendor_id)
- INDEX (status)
- INDEX (po_date)

**Relationships:**
- belongs to Project
- belongs to Vendor
- belongs to User (creator)
- belongs to User (approver)
- morphs many Comments
- morphs many Documents

---

### 12. Bank_Accounts Table

**Purpose:** Organizational bank account tracking

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| account_name | VARCHAR(255) | Account name |
| account_number | VARCHAR(255) | Unique account number |
| bank_name | VARCHAR(255) | Bank name |
| branch | VARCHAR(255) | Bank branch |
| currency | VARCHAR(3) | Currency code (default: USD) |
| current_balance | DECIMAL(15,2) | Current account balance |
| is_active | BOOLEAN | Whether account is active |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (account_number)
- INDEX (account_number)
- INDEX (is_active)

**Relationships:**
- has many CashFlow entries

---

### 13. Cash_Flow Table

**Purpose:** Track all cash inflows and outflows with balance tracking

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| transaction_date | DATE | Transaction date |
| type | ENUM | cash_in, cash_out |
| amount | DECIMAL(15,2) | Transaction amount |
| description | TEXT | Transaction description |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| donor_id | BIGINT UNSIGNED (FK) | References donors.id |
| expense_id | BIGINT UNSIGNED (FK) | References expenses.id |
| bank_account_id | BIGINT UNSIGNED (FK) | References bank_accounts.id |
| balance_before | DECIMAL(15,2) | Balance before transaction |
| balance_after | DECIMAL(15,2) | Balance after transaction |
| created_by | BIGINT UNSIGNED (FK) | References users.id |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (transaction_date)
- INDEX (type)
- INDEX (project_id)
- INDEX (bank_account_id)
- COMPOSITE INDEX (transaction_date, type)

**Relationships:**
- belongs to Project
- belongs to Donor
- belongs to Expense
- belongs to BankAccount
- belongs to User (creator)

---

### 14. In_Kind_Contributions Table

**Purpose:** Track non-monetary donor contributions

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| project_id | BIGINT UNSIGNED (FK) | References projects.id |
| donor_id | BIGINT UNSIGNED (FK) | References donors.id |
| description | TEXT | Contribution description |
| estimated_value | DECIMAL(15,2) | Estimated monetary value |
| contribution_date | DATE | Date of contribution |
| category | VARCHAR(255) | Contribution category |
| created_by | BIGINT UNSIGNED (FK) | References users.id |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (donor_id)
- INDEX (contribution_date)

**Relationships:**
- belongs to Project
- belongs to Donor
- belongs to User (creator)

---

## System Entities

### 15. Comments Table

**Purpose:** Polymorphic commenting system for multiple entity types

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| commentable_type | VARCHAR(255) | Model type (polymorphic) |
| commentable_id | BIGINT UNSIGNED | Model ID (polymorphic) |
| user_id | BIGINT UNSIGNED (FK) | References users.id |
| parent_id | BIGINT UNSIGNED (FK) | References comments.id (for replies) |
| comment | TEXT | Comment text |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- COMPOSITE INDEX (commentable_type, commentable_id)
- INDEX (user_id)
- INDEX (parent_id)

**Relationships:**
- belongs to User
- belongs to Comment (parent)
- has many Comments (replies)
- has many CommentAttachments
- morphs to multiple models (Projects, Expenses, PurchaseOrders, etc.)

---

### 16. Comment_Attachments Table

**Purpose:** File attachments for comments

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| comment_id | BIGINT UNSIGNED (FK) | References comments.id |
| file_name | VARCHAR(255) | Original file name |
| file_path | VARCHAR(255) | Storage file path |
| file_type | VARCHAR(255) | MIME type |
| file_size | INT UNSIGNED | File size in bytes |
| created_at | TIMESTAMP | Upload timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (comment_id)

**Relationships:**
- belongs to Comment

---

### 17. Documents Table

**Purpose:** Polymorphic document management for multiple entity types

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| documentable_type | VARCHAR(255) | Model type (polymorphic) |
| documentable_id | BIGINT UNSIGNED | Model ID (polymorphic) |
| title | VARCHAR(255) | Document title |
| description | TEXT | Document description |
| file_name | VARCHAR(255) | Original file name |
| file_path | VARCHAR(255) | Storage file path |
| file_type | VARCHAR(255) | MIME type |
| file_size | INT UNSIGNED | File size in bytes |
| uploaded_by | BIGINT UNSIGNED (FK) | References users.id |
| created_at | TIMESTAMP | Upload timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- COMPOSITE INDEX (documentable_type, documentable_id)
- INDEX (uploaded_by)
- INDEX (file_type)

**Relationships:**
- belongs to User (uploader)
- morphs to multiple models (Projects, Expenses, PurchaseOrders, etc.)

---

### 18. Audit_Trails Table

**Purpose:** Comprehensive audit logging of all data changes

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| user_id | BIGINT UNSIGNED (FK) | References users.id |
| action | VARCHAR(255) | Action performed (created, updated, deleted) |
| auditable_type | VARCHAR(255) | Model type (polymorphic) |
| auditable_id | BIGINT UNSIGNED | Model ID (polymorphic) |
| old_values | JSON | Previous values |
| new_values | JSON | New values |
| ip_address | VARCHAR(45) | User IP address |
| user_agent | TEXT | User browser/agent |
| created_at | TIMESTAMP | Audit timestamp |

**Indexes:**
- PRIMARY KEY (id)
- COMPOSITE INDEX (auditable_type, auditable_id)
- INDEX (user_id)
- INDEX (action)
- INDEX (created_at)

**Relationships:**
- belongs to User
- morphs to all auditable models

---

### 19. Activity_Logs Table

**Purpose:** User activity tracking for system monitoring

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| user_id | BIGINT UNSIGNED (FK) | References users.id |
| activity_type | VARCHAR(255) | Type of activity |
| description | TEXT | Activity description |
| properties | JSON | Additional properties |
| created_at | TIMESTAMP | Activity timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (activity_type)
- INDEX (created_at)
- COMPOSITE INDEX (user_id, created_at)

**Relationships:**
- belongs to User

---

### 20. Notifications Table

**Purpose:** User notification system

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| user_id | BIGINT UNSIGNED (FK) | References users.id |
| type | VARCHAR(255) | Notification type |
| title | VARCHAR(255) | Notification title |
| message | TEXT | Notification message |
| data | JSON | Additional data |
| read_at | TIMESTAMP | Read timestamp |
| created_at | TIMESTAMP | Notification creation |
| updated_at | TIMESTAMP | Last update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (type)
- INDEX (read_at)
- COMPOSITE INDEX (user_id, read_at)

**Relationships:**
- belongs to User

---

### 21. System_Settings Table

**Purpose:** Configurable system-wide settings

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| key | VARCHAR(255) | Setting key (unique) |
| value | TEXT | Setting value |
| type | VARCHAR(255) | Value type (string, integer, boolean, json) |
| description | TEXT | Setting description |
| created_at | TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (key)
- INDEX (key)

**Default Settings:**
- org_name: Climate Action Network Zimbabwe
- org_short_name: CANZIM
- org_logo: /images/logo/canzim_logo.png
- currency: USD
- timezone: Africa/Harare
- session_timeout: 5
- date_format: d/m/Y
- datetime_format: d/m/Y H:i
- max_file_size_documents: 5120 (5MB)
- max_file_size_receipts: 5120 (5MB)
- max_file_size_attachments: 2048 (2MB)

---

### 22. Reports Table

**Purpose:** Track generated reports

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| report_type | VARCHAR(255) | Type of report |
| title | VARCHAR(255) | Report title |
| parameters | JSON | Report generation parameters |
| file_path | VARCHAR(255) | Generated file path |
| generated_by | BIGINT UNSIGNED (FK) | References users.id |
| status | ENUM | pending, processing, completed, failed |
| created_at | TIMESTAMP | Report request timestamp |
| updated_at | TIMESTAMP | Last update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (report_type)
- INDEX (generated_by)
- INDEX (status)
- INDEX (created_at)

**Relationships:**
- belongs to User (generator)

---

## Relationships Summary

### One-to-Many Relationships

```
users → projects (creator)
users → budgets (creator)
users → expenses (submitter)
users → comments
users → documents (uploader)
users → notifications
users → activity_logs
users → audit_trails
users → reports

roles → users

projects → budgets
projects → expenses
projects → purchase_orders
projects → in_kind_contributions
projects → cash_flow

budgets → budget_items

expense_categories → expenses

budget_items → expenses

vendors → purchase_orders

bank_accounts → cash_flow

comments → comment_attachments
comments → comments (parent-child)
```

### Many-to-Many Relationships

```
projects ↔ donors (through project_donors pivot table)
```

### Polymorphic Relationships

```
comments → morphs to: projects, expenses, purchase_orders, budgets
documents → morphs to: projects, expenses, purchase_orders, budgets
audit_trails → morphs to: all auditable models
```

### Foreign Key Constraints

All foreign key relationships implement proper constraints:

**ON DELETE CASCADE:**
- When parent is deleted, child records are also deleted
- Examples: budget → budget_items, comment → comment_attachments

**ON DELETE SET NULL:**
- When parent is deleted, foreign key is set to NULL
- Examples: users → projects (approved_by), expenses (reviewed_by)

---

## ERD Diagram

```
┌────────────────┐         ┌────────────────┐
│     ROLES      │◄────────┤     USERS      │
│                │  1:Many │                │
│ • id           │         │ • id           │
│ • name         │         │ • name         │
│ • slug         │         │ • email        │
│ • description  │         │ • password     │
└────────────────┘         │ • role_id (FK) │
                           │ • office_loc   │
                           │ • status       │
                           └────────┬───────┘
                                    │ 1:Many
                                    │
                    ┌───────────────┴────────────────┬──────────────┐
                    │                                │              │
            ┌───────▼────────┐              ┌───────▼────────┐     │
            │   PROJECTS     │              │    BUDGETS     │     │
            │                │◄─────────────┤                │     │
            │ • id           │   1:Many     │ • id           │     │
            │ • code         │              │ • project_id   │     │
            │ • name         │              │ • fiscal_year  │     │
            │ • description  │              │ • total_amount │     │
            │ • start_date   │              │ • status       │     │
            │ • end_date     │              │ • approved_by  │     │
            │ • total_budget │              │ • created_by   │     │
            │ • status       │              └────────┬───────┘
            │ • created_by   │                       │ 1:Many
            └────┬──┬────┬───┘                       │
                 │  │    │                  ┌────────▼───────────┐
                 │  │    │                  │   BUDGET_ITEMS     │
                 │  │    │                  │                    │
                 │  │    │                  │ • id               │
                 │  │    │                  │ • budget_id (FK)   │
                 │  │    │                  │ • category         │
                 │  │    │                  │ • description      │
                 │  │    │                  │ • cost_code        │
                 │  │    │                  │ • allocated_amount │
                 │  │    │                  │ • spent_amount     │
                 │  │    │                  │ • remaining_amount │
                 │  │    │                  └────────────────────┘
                 │  │    │
                 │  │    │ Many:Many                    ┌──────────────────┐
                 │  │    └──────────┐                   │ EXPENSE_CATEGORIES│
                 │  │               │                   │                  │
                 │  │      ┌────────▼──────────┐        │ • id             │
                 │  │      │  PROJECT_DONORS   │        │ • name           │
                 │  │      │                   │        │ • slug           │
                 │  │      │ • id              │        │ • description    │
                 │  │      │ • project_id (FK) │◄───┐   │ • is_active      │
                 │  │      │ • donor_id (FK)   │    │   └───────┬──────────┘
                 │  │      │ • funding_amount  │    │           │ 1:Many
                 │  │      │ • period_start    │    │           │
                 │  │      │ • period_end      │    │  ┌────────▼──────────┐
                 │  │      │ • is_restricted   │    │  │    EXPENSES       │
                 │  │      └───────────────────┘    │  │                   │
                 │  │                               │  │ • id              │
                 │  │      ┌────────────────┐       │  │ • project_id (FK) │
                 │  └──────►    DONORS      ├───────┘  │ • budget_item_id  │
                 │  1:Many │                │          │ • category_id (FK)│
                 │         │ • id           │          │ • expense_date    │
                 │         │ • name         │          │ • amount          │
                 │         │ • contact      │          │ • description     │
                 │         │ • email        │          │ • receipt_path    │
                 │         │ • phone        │          │ • status          │
                 │         │ • address      │          │ • submitted_by    │
                 │         │ • funding_total│          │ • reviewed_by     │
                 │         └────┬───────────┘          │ • approved_by     │
                 │              │ 1:Many               │ • submitted_at    │
                 │              │                      │ • reviewed_at     │
                 │    ┌─────────▼──────────────┐       │ • approved_at     │
                 │    │ IN_KIND_CONTRIBUTIONS  │       │ • rejection_reason│
                 │    │                        │       └───────────────────┘
                 │    │ • id                   │
                 │    │ • project_id (FK)      │
                 │    │ • donor_id (FK)        │       ┌──────────────────┐
                 │    │ • description          │       │     VENDORS      │
                 │    │ • estimated_value      │       │                  │
                 │    │ • contribution_date    │       │ • id             │
                 │    │ • category             │       │ • name           │
                 │    │ • created_by (FK)      │       │ • contact_person │
                 │    └────────────────────────┘       │ • email          │
                 │                                     │ • phone          │
                 │                                     │ • address        │
                 │ 1:Many                              │ • tax_id         │
                 │                                     └────┬─────────────┘
      ┌──────────▼───────────┐                             │ 1:Many
      │  PURCHASE_ORDERS     │                             │
      │                      │◄────────────────────────────┘
      │ • id                 │
      │ • project_id (FK)    │
      │ • vendor_id (FK)     │
      │ • po_number          │
      │ • po_date            │
      │ • total_amount       │
      │ • status             │
      │ • description        │
      │ • created_by (FK)    │
      │ • approved_by (FK)   │
      │ • approved_at        │
      └──────────────────────┘

┌──────────────────┐         ┌──────────────────┐
│  BANK_ACCOUNTS   │         │    CASH_FLOW     │
│                  │◄────────┤                  │
│ • id             │  1:Many │ • id             │
│ • account_name   │         │ • transaction_dt │
│ • account_number │         │ • type           │
│ • bank_name      │         │ • amount         │
│ • branch         │         │ • description    │
│ • currency       │         │ • project_id     │
│ • current_balance│         │ • donor_id       │
│ • is_active      │         │ • expense_id     │
└──────────────────┘         │ • bank_account_id│
                             │ • balance_before │
                             │ • balance_after  │
                             │ • created_by     │
                             └──────────────────┘

POLYMORPHIC RELATIONSHIPS:

┌──────────────────┐         ┌──────────────────┐
│    COMMENTS      │         │    DOCUMENTS     │
│                  │         │                  │
│ • id             │         │ • id             │
│ • commentable_id │         │ • documentable_id│
│ • commentable_type│        │ • documentable_type
│ • user_id (FK)   │         │ • title          │
│ • parent_id      │         │ • description    │
│ • comment        │         │ • file_name      │
└────┬─────────────┘         │ • file_path      │
     │ 1:Many                │ • file_type      │
     │                       │ • file_size      │
┌────▼──────────────┐        │ • uploaded_by    │
│COMMENT_ATTACHMENTS│        └──────────────────┘
│                   │         Morphs To:
│ • id              │         • Projects
│ • comment_id (FK) │         • Expenses
│ • file_name       │         • Purchase Orders
│ • file_path       │         • Budgets
│ • file_type       │
│ • file_size       │
└───────────────────┘

SYSTEM & AUDIT TABLES:

┌──────────────────┐   ┌──────────────────┐   ┌──────────────────┐
│  AUDIT_TRAILS    │   │  ACTIVITY_LOGS   │   │  NOTIFICATIONS   │
│                  │   │                  │   │                  │
│ • id             │   │ • id             │   │ • id             │
│ • user_id        │   │ • user_id        │   │ • user_id        │
│ • action         │   │ • activity_type  │   │ • type           │
│ • auditable_type │   │ • description    │   │ • title          │
│ • auditable_id   │   │ • properties     │   │ • message        │
│ • old_values     │   │ • created_at     │   │ • data           │
│ • new_values     │   └──────────────────┘   │ • read_at        │
│ • ip_address     │                          └──────────────────┘
│ • user_agent     │
│ • created_at     │
└──────────────────┘

┌──────────────────┐   ┌──────────────────┐
│ SYSTEM_SETTINGS  │   │     REPORTS      │
│                  │   │                  │
│ • id             │   │ • id             │
│ • key            │   │ • report_type    │
│ • value          │   │ • title          │
│ • type           │   │ • parameters     │
│ • description    │   │ • file_path      │
└──────────────────┘   │ • generated_by   │
                       │ • status         │
                       └──────────────────┘
```

---

## Database Statistics

**Total Tables:** 26
- Core Tables: 5 (users, roles, projects, donors, project_donors)
- Financial Tables: 9 (budgets, budget_items, expenses, expense_categories, vendors, purchase_orders, bank_accounts, cash_flow, in_kind_contributions)
- System Tables: 6 (comments, comment_attachments, documents, audit_trails, activity_logs, notifications)
- Configuration Tables: 2 (system_settings, reports)
- Laravel Default Tables: 4 (cache, jobs, personal_access_tokens, password_reset_tokens)

**Total Foreign Keys:** 45+

**Total Indexes:** 80+ (including primary keys, foreign keys, unique indexes, and composite indexes)

**Soft Delete Enabled:** 11 tables (projects, donors, budgets, expenses, vendors, comments, documents, and others)

---

## Migration Execution Order

Migrations must be executed in dependency order to avoid foreign key constraint errors:

1. **Base Tables** (no dependencies)
   - roles
   - users (base structure)
   - expense_categories
   - vendors
   - bank_accounts
   - system_settings

2. **Dependent Tables** (first level)
   - users (role_id foreign key)
   - projects
   - donors
   - budgets

3. **Second Level Dependencies**
   - project_donors (pivot)
   - budget_items
   - expenses
   - purchase_orders
   - cash_flow
   - in_kind_contributions

4. **Polymorphic & System Tables**
   - comments
   - comment_attachments
   - documents
   - audit_trails
   - activity_logs
   - notifications
   - reports

---

## Best Practices Implemented

1. **Normalization**: Schema follows Third Normal Form (3NF)
2. **Referential Integrity**: All foreign keys properly constrained
3. **Performance**: Strategic indexes on frequently queried columns
4. **Audit Trail**: Comprehensive tracking of all changes
5. **Soft Deletes**: Data recovery capability on critical tables
6. **Timestamps**: created_at and updated_at on all tables
7. **Data Types**: Appropriate use of DECIMAL for financial amounts
8. **Naming Conventions**: Consistent snake_case throughout
9. **Polymorphic Relationships**: Flexible attachment of comments/documents
10. **Approval Workflows**: Built-in support for multi-tier approvals

---

**End of ERD Documentation**
