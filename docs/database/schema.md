# CANZIM FinTrack - Database Schema Documentation

**Version:** 1.0.0  
**Last Updated:** November 14, 2025  
**Database:** my_canzimdb  
**Module:** Database Design & Migrations

---

## 📋 Table of Contents

1. [Database Overview](#database-overview)
2. [Migration Files](#migration-files)
3. [Seeder Files](#seeder-files)
4. [Complete Schema Reference](#complete-schema-reference)
5. [Index Strategy](#index-strategy)
6. [Foreign Key Relationships](#foreign-key-relationships)
7. [Data Types & Constraints](#data-types--constraints)
8. [Seeded Data](#seeded-data)

---

## Database Overview

**Database Name:** my_canzimdb  
**Charset:** utf8mb4  
**Collation:** utf8mb4_unicode_ci  
**Engine:** InnoDB (MySQL 8.0+)  
**Total Tables:** 26

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_canzimdb
DB_USERNAME=root
DB_PASSWORD=
```

---

## Migration Files

All migration files are located in `database/migrations/` and are executed in chronological order based on timestamp.

### Migration Execution Order

| #   | Timestamp         | Migration File                          | Purpose                         |
| --- | ----------------- | --------------------------------------- | ------------------------------- |
| 1   | 0001_01_01_000000 | create_users_table.php                  | Laravel default users table     |
| 2   | 0001_01_01_000001 | create_cache_table.php                  | Laravel cache table             |
| 3   | 0001_01_01_000002 | create_jobs_table.php                   | Laravel queue jobs table        |
| 4   | 2025_11_14_110249 | create_personal_access_tokens_table.php | Sanctum API tokens              |
| 5   | 2025_11_14_113014 | create_roles_table.php                  | User roles                      |
| 6   | 2025_11_14_113021 | update_users_table_add_role_fields.php  | Add role to users               |
| 7   | 2025_11_14_113027 | create_projects_table.php               | Projects (financial containers) |
| 8   | 2025_11_14_113028 | create_budgets_table.php                | Project budgets                 |
| 9   | 2025_11_14_113028 | create_donors_table.php                 | Donor organizations             |
| 10  | 2025_11_14_113028 | create_project_donors_table.php         | Project-donor pivot             |
| 11  | 2025_11_14_113029 | create_budget_items_table.php           | Budget line items               |
| 12  | 2025_11_14_113034 | create_expense_categories_table.php     | Expense categories              |
| 13  | 2025_11_14_113035 | create_expenses_table.php               | Expense transactions            |
| 14  | 2025_11_14_113035 | create_vendors_table.php                | Vendor/supplier data            |
| 15  | 2025_11_14_113036 | create_bank_accounts_table.php          | Bank account tracking           |
| 16  | 2025_11_14_113036 | create_purchase_orders_table.php        | Purchase orders                 |
| 17  | 2025_11_14_113044 | create_cash_flow_table.php              | Cash flow tracking              |
| 18  | 2025_11_14_113044 | create_comments_table.php               | Polymorphic comments            |
| 19  | 2025_11_14_113044 | create_in_kind_contributions_table.php  | Non-monetary contributions      |
| 20  | 2025_11_14_113045 | create_comment_attachments_table.php    | Comment file attachments        |
| 21  | 2025_11_14_113045 | create_documents_table.php              | Polymorphic documents           |
| 22  | 2025_11_14_113057 | create_activity_logs_table.php          | User activity tracking          |
| 23  | 2025_11_14_113057 | create_audit_trails_table.php           | Audit trail logging             |
| 24  | 2025_11_14_113057 | create_notifications_table.php          | User notifications              |
| 25  | 2025_11_14_113058 | create_reports_table.php                | Generated reports tracking      |
| 26  | 2025_11_14_113058 | create_system_settings_table.php        | System configuration            |

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run migrations with seeding
php artisan migrate --seed

# Fresh migration (drop all tables and re-migrate)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Check migration status
php artisan migrate:status
```

---

## Seeder Files

All seeder files are located in `database/seeders/`.

### Available Seeders

| Seeder Class            | Purpose                           | Records Created |
| ----------------------- | --------------------------------- | --------------- |
| RolesSeeder             | Create default user roles         | 3 roles         |
| ExpenseCategoriesSeeder | Create default expense categories | 5 categories    |
| AdminUserSeeder         | Create default admin account      | 1 user          |
| SystemSettingsSeeder    | Create default system settings    | 11 settings     |

### Running Seeders

```bash
# Run all seeders (defined in DatabaseSeeder)
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=ExpenseCategoriesSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=SystemSettingsSeeder
```

---

## Complete Schema Reference

### 1. users

**Purpose:** User account management with role-based access control

| Column            | Type            | Attributes                  | Description                  |
| ----------------- | --------------- | --------------------------- | ---------------------------- |
| id                | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier            |
| name              | VARCHAR(255)    | NOT NULL                    | Full name                    |
| email             | VARCHAR(255)    | NOT NULL, UNIQUE            | Email address                |
| email_verified_at | TIMESTAMP       | NULLABLE                    | Email verification timestamp |
| password          | VARCHAR(255)    | NOT NULL                    | Hashed password              |
| role_id           | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References roles.id          |
| office_location   | VARCHAR(255)    | NULLABLE                    | User office location         |
| status            | ENUM            | DEFAULT 'active'            | active, inactive, suspended  |
| remember_token    | VARCHAR(100)    | NULLABLE                    | Remember me token            |
| created_at        | TIMESTAMP       | NULLABLE                    | Creation timestamp           |
| updated_at        | TIMESTAMP       | NULLABLE                    | Last update timestamp        |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (email)
- INDEX (role_id)
- INDEX (status)

**Foreign Keys:**

- role_id → roles(id) ON DELETE SET NULL

---

### 2. roles

**Purpose:** User role definitions for access control

| Column      | Type            | Attributes                  | Description           |
| ----------- | --------------- | --------------------------- | --------------------- |
| id          | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| name        | VARCHAR(255)    | NOT NULL, UNIQUE            | Role name             |
| slug        | VARCHAR(255)    | NOT NULL, UNIQUE            | URL-friendly slug     |
| description | TEXT            | NULLABLE                    | Role description      |
| created_at  | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at  | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (name)
- UNIQUE KEY (slug)
- INDEX (slug)

---

### 3. projects

**Purpose:** Project tracking as financial containers for budget and expense allocation

| Column          | Type            | Attributes                  | Description                                     |
| --------------- | --------------- | --------------------------- | ----------------------------------------------- |
| id              | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                               |
| code            | VARCHAR(255)    | NOT NULL, UNIQUE            | Project code                                    |
| name            | VARCHAR(255)    | NOT NULL                    | Project name                                    |
| description     | TEXT            | NULLABLE                    | Project description                             |
| start_date      | DATE            | NOT NULL                    | Project start date                              |
| end_date        | DATE            | NOT NULL                    | Project end date                                |
| total_budget    | DECIMAL(15,2)   | DEFAULT 0.00                | Total budget amount                             |
| status          | ENUM            | DEFAULT 'planning'          | planning, active, on_hold, completed, cancelled |
| office_location | VARCHAR(255)    | NULLABLE                    | Project office location                         |
| created_by      | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                             |
| created_at      | TIMESTAMP       | NULLABLE                    | Creation timestamp                              |
| updated_at      | TIMESTAMP       | NULLABLE                    | Last update timestamp                           |
| deleted_at      | TIMESTAMP       | NULLABLE                    | Soft delete timestamp                           |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (code)
- INDEX (status)
- INDEX (start_date)
- INDEX (end_date)
- INDEX (created_by)

**Foreign Keys:**

- created_by → users(id) ON DELETE CASCADE

---

### 4. donors

**Purpose:** Donor organization information and funding tracking

| Column         | Type            | Attributes                  | Description            |
| -------------- | --------------- | --------------------------- | ---------------------- |
| id             | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier      |
| name           | VARCHAR(255)    | NOT NULL                    | Donor name             |
| contact_person | VARCHAR(255)    | NULLABLE                    | Contact person name    |
| email          | VARCHAR(255)    | NULLABLE                    | Contact email          |
| phone          | VARCHAR(255)    | NULLABLE                    | Contact phone          |
| address        | TEXT            | NULLABLE                    | Donor address          |
| funding_total  | DECIMAL(15,2)   | DEFAULT 0.00                | Total funding provided |
| created_at     | TIMESTAMP       | NULLABLE                    | Creation timestamp     |
| updated_at     | TIMESTAMP       | NULLABLE                    | Last update timestamp  |
| deleted_at     | TIMESTAMP       | NULLABLE                    | Soft delete timestamp  |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (name)
- INDEX (email)

---

### 5. project_donors

**Purpose:** Many-to-many relationship between projects and donors with funding details

| Column               | Type            | Attributes                  | Description             |
| -------------------- | --------------- | --------------------------- | ----------------------- |
| id                   | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier       |
| project_id           | BIGINT UNSIGNED | FOREIGN KEY                 | References projects.id  |
| donor_id             | BIGINT UNSIGNED | FOREIGN KEY                 | References donors.id    |
| funding_amount       | DECIMAL(15,2)   | DEFAULT 0.00                | Funding amount          |
| funding_period_start | DATE            | NULLABLE                    | Funding period start    |
| funding_period_end   | DATE            | NULLABLE                    | Funding period end      |
| is_restricted        | BOOLEAN         | DEFAULT FALSE               | Restricted funding flag |
| created_at           | TIMESTAMP       | NULLABLE                    | Creation timestamp      |
| updated_at           | TIMESTAMP       | NULLABLE                    | Last update timestamp   |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (donor_id)
- UNIQUE KEY (project_id, donor_id)

**Foreign Keys:**

- project_id → projects(id) ON DELETE CASCADE
- donor_id → donors(id) ON DELETE CASCADE

---

### 6. budgets

**Purpose:** Project budget allocation with approval workflow

| Column       | Type            | Attributes                  | Description                          |
| ------------ | --------------- | --------------------------- | ------------------------------------ |
| id           | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                    |
| project_id   | BIGINT UNSIGNED | FOREIGN KEY                 | References projects.id               |
| fiscal_year  | VARCHAR(255)    | NOT NULL                    | Fiscal year                          |
| total_amount | DECIMAL(15,2)   | DEFAULT 0.00                | Total budget amount                  |
| status       | ENUM            | DEFAULT 'draft'             | draft, submitted, approved, rejected |
| approved_by  | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id                  |
| approved_at  | TIMESTAMP       | NULLABLE                    | Approval timestamp                   |
| created_by   | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                  |
| created_at   | TIMESTAMP       | NULLABLE                    | Creation timestamp                   |
| updated_at   | TIMESTAMP       | NULLABLE                    | Last update timestamp                |
| deleted_at   | TIMESTAMP       | NULLABLE                    | Soft delete timestamp                |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (fiscal_year)
- INDEX (status)
- INDEX (project_id, fiscal_year)

**Foreign Keys:**

- project_id → projects(id) ON DELETE CASCADE
- created_by → users(id) ON DELETE CASCADE
- approved_by → users(id) ON DELETE SET NULL

---

### 7. budget_items

**Purpose:** Detailed budget line items within project budgets

| Column           | Type            | Attributes                  | Description           |
| ---------------- | --------------- | --------------------------- | --------------------- |
| id               | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| budget_id        | BIGINT UNSIGNED | FOREIGN KEY                 | References budgets.id |
| category         | VARCHAR(255)    | NOT NULL                    | Budget category       |
| description      | TEXT            | NULLABLE                    | Item description      |
| cost_code        | VARCHAR(255)    | NULLABLE                    | Cost code             |
| allocated_amount | DECIMAL(15,2)   | DEFAULT 0.00                | Allocated amount      |
| spent_amount     | DECIMAL(15,2)   | DEFAULT 0.00                | Spent amount          |
| remaining_amount | DECIMAL(15,2)   | DEFAULT 0.00                | Remaining amount      |
| created_at       | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at       | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (budget_id)
- INDEX (category)
- INDEX (cost_code)

**Foreign Keys:**

- budget_id → budgets(id) ON DELETE CASCADE

---

### 8. expense_categories

**Purpose:** Standardized expense classification

| Column      | Type            | Attributes                  | Description           |
| ----------- | --------------- | --------------------------- | --------------------- |
| id          | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| name        | VARCHAR(255)    | NOT NULL, UNIQUE            | Category name         |
| slug        | VARCHAR(255)    | NOT NULL, UNIQUE            | URL-friendly slug     |
| description | TEXT            | NULLABLE                    | Category description  |
| is_active   | BOOLEAN         | DEFAULT TRUE                | Active status         |
| created_at  | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at  | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (name)
- UNIQUE KEY (slug)
- INDEX (slug)
- INDEX (is_active)

---

### 9. expenses

**Purpose:** Expense transaction tracking with multi-tier approval workflow

| Column           | Type            | Attributes                  | Description                                              |
| ---------------- | --------------- | --------------------------- | -------------------------------------------------------- |
| id               | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                                        |
| project_id       | BIGINT UNSIGNED | FOREIGN KEY                 | References projects.id                                   |
| budget_item_id   | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References budget_items.id                               |
| category_id      | BIGINT UNSIGNED | FOREIGN KEY                 | References expense_categories.id                         |
| expense_date     | DATE            | NOT NULL                    | Expense date                                             |
| amount           | DECIMAL(15,2)   | NOT NULL                    | Expense amount                                           |
| description      | TEXT            | NOT NULL                    | Expense description                                      |
| receipt_path     | VARCHAR(255)    | NULLABLE                    | Receipt file path                                        |
| status           | ENUM            | DEFAULT 'draft'             | draft, submitted, under_review, approved, rejected, paid |
| submitted_by     | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                                      |
| reviewed_by      | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id                                      |
| approved_by      | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id                                      |
| submitted_at     | TIMESTAMP       | NULLABLE                    | Submission timestamp                                     |
| reviewed_at      | TIMESTAMP       | NULLABLE                    | Review timestamp                                         |
| approved_at      | TIMESTAMP       | NULLABLE                    | Approval timestamp                                       |
| rejection_reason | TEXT            | NULLABLE                    | Rejection reason                                         |
| created_at       | TIMESTAMP       | NULLABLE                    | Creation timestamp                                       |
| updated_at       | TIMESTAMP       | NULLABLE                    | Last update timestamp                                    |
| deleted_at       | TIMESTAMP       | NULLABLE                    | Soft delete timestamp                                    |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (category_id)
- INDEX (expense_date)
- INDEX (status)
- INDEX (submitted_by)
- INDEX (project_id, status)
- INDEX (expense_date, status)

**Foreign Keys:**

- project_id → projects(id) ON DELETE CASCADE
- budget_item_id → budget_items(id) ON DELETE SET NULL
- category_id → expense_categories(id) ON DELETE CASCADE
- submitted_by → users(id) ON DELETE CASCADE
- reviewed_by → users(id) ON DELETE SET NULL
- approved_by → users(id) ON DELETE SET NULL

---

### 10. vendors

**Purpose:** Vendor and supplier information management

| Column         | Type            | Attributes                  | Description           |
| -------------- | --------------- | --------------------------- | --------------------- |
| id             | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| name           | VARCHAR(255)    | NOT NULL                    | Vendor name           |
| contact_person | VARCHAR(255)    | NULLABLE                    | Contact person        |
| email          | VARCHAR(255)    | NULLABLE                    | Contact email         |
| phone          | VARCHAR(255)    | NULLABLE                    | Contact phone         |
| address        | TEXT            | NULLABLE                    | Vendor address        |
| tax_id         | VARCHAR(255)    | NULLABLE                    | Tax ID                |
| created_at     | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at     | TIMESTAMP       | NULLABLE                    | Last update timestamp |
| deleted_at     | TIMESTAMP       | NULLABLE                    | Soft delete timestamp |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (name)
- INDEX (email)

---

### 11. purchase_orders

**Purpose:** Purchase order tracking with approval workflow

| Column       | Type            | Attributes                  | Description                                              |
| ------------ | --------------- | --------------------------- | -------------------------------------------------------- |
| id           | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                                        |
| project_id   | BIGINT UNSIGNED | FOREIGN KEY                 | References projects.id                                   |
| vendor_id    | BIGINT UNSIGNED | FOREIGN KEY                 | References vendors.id                                    |
| po_number    | VARCHAR(255)    | NOT NULL, UNIQUE            | PO number                                                |
| po_date      | DATE            | NOT NULL                    | PO date                                                  |
| total_amount | DECIMAL(15,2)   | NOT NULL                    | Total amount                                             |
| status       | ENUM            | DEFAULT 'draft'             | draft, pending, approved, rejected, completed, cancelled |
| description  | TEXT            | NULLABLE                    | PO description                                           |
| created_by   | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                                      |
| approved_by  | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id                                      |
| approved_at  | TIMESTAMP       | NULLABLE                    | Approval timestamp                                       |
| created_at   | TIMESTAMP       | NULLABLE                    | Creation timestamp                                       |
| updated_at   | TIMESTAMP       | NULLABLE                    | Last update timestamp                                    |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (po_number)
- INDEX (po_number)
- INDEX (project_id)
- INDEX (vendor_id)
- INDEX (status)
- INDEX (po_date)

**Foreign Keys:**

- project_id → projects(id) ON DELETE CASCADE
- vendor_id → vendors(id) ON DELETE CASCADE
- created_by → users(id) ON DELETE CASCADE
- approved_by → users(id) ON DELETE SET NULL

---

### 12. bank_accounts

**Purpose:** Organizational bank account tracking

| Column          | Type            | Attributes                  | Description           |
| --------------- | --------------- | --------------------------- | --------------------- |
| id              | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| account_name    | VARCHAR(255)    | NOT NULL                    | Account name          |
| account_number  | VARCHAR(255)    | NOT NULL, UNIQUE            | Account number        |
| bank_name       | VARCHAR(255)    | NOT NULL                    | Bank name             |
| branch          | VARCHAR(255)    | NULLABLE                    | Branch name           |
| currency        | VARCHAR(3)      | DEFAULT 'USD'               | Currency code         |
| current_balance | DECIMAL(15,2)   | DEFAULT 0.00                | Current balance       |
| is_active       | BOOLEAN         | DEFAULT TRUE                | Active status         |
| created_at      | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at      | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (account_number)
- INDEX (account_number)
- INDEX (is_active)

---

### 13. cash_flow

**Purpose:** Cash flow tracking with balance management

| Column           | Type            | Attributes                  | Description                 |
| ---------------- | --------------- | --------------------------- | --------------------------- |
| id               | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier           |
| transaction_date | DATE            | NOT NULL                    | Transaction date            |
| type             | ENUM            | NOT NULL                    | cash_in, cash_out           |
| amount           | DECIMAL(15,2)   | NOT NULL                    | Transaction amount          |
| description      | TEXT            | NOT NULL                    | Description                 |
| project_id       | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References projects.id      |
| donor_id         | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References donors.id        |
| expense_id       | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References expenses.id      |
| bank_account_id  | BIGINT UNSIGNED | FOREIGN KEY                 | References bank_accounts.id |
| balance_before   | DECIMAL(15,2)   | DEFAULT 0.00                | Balance before transaction  |
| balance_after    | DECIMAL(15,2)   | DEFAULT 0.00                | Balance after transaction   |
| created_by       | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id         |
| created_at       | TIMESTAMP       | NULLABLE                    | Creation timestamp          |
| updated_at       | TIMESTAMP       | NULLABLE                    | Last update timestamp       |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (transaction_date)
- INDEX (type)
- INDEX (project_id)
- INDEX (bank_account_id)
- INDEX (transaction_date, type)

**Foreign Keys:**

- project_id → projects(id) ON DELETE SET NULL
- donor_id → donors(id) ON DELETE SET NULL
- expense_id → expenses(id) ON DELETE SET NULL
- bank_account_id → bank_accounts(id) ON DELETE CASCADE
- created_by → users(id) ON DELETE CASCADE

---

### 14. in_kind_contributions

**Purpose:** Track non-monetary donor contributions

| Column            | Type            | Attributes                  | Description              |
| ----------------- | --------------- | --------------------------- | ------------------------ |
| id                | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier        |
| project_id        | BIGINT UNSIGNED | FOREIGN KEY                 | References projects.id   |
| donor_id          | BIGINT UNSIGNED | FOREIGN KEY                 | References donors.id     |
| description       | TEXT            | NOT NULL                    | Contribution description |
| estimated_value   | DECIMAL(15,2)   | NOT NULL                    | Estimated value          |
| contribution_date | DATE            | NOT NULL                    | Contribution date        |
| category          | VARCHAR(255)    | NULLABLE                    | Category                 |
| created_by        | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id      |
| created_at        | TIMESTAMP       | NULLABLE                    | Creation timestamp       |
| updated_at        | TIMESTAMP       | NULLABLE                    | Last update timestamp    |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (project_id)
- INDEX (donor_id)
- INDEX (contribution_date)

**Foreign Keys:**

- project_id → projects(id) ON DELETE CASCADE
- donor_id → donors(id) ON DELETE CASCADE
- created_by → users(id) ON DELETE CASCADE

---

### 15. comments

**Purpose:** Polymorphic commenting system for multiple entity types

| Column           | Type            | Attributes                  | Description                          |
| ---------------- | --------------- | --------------------------- | ------------------------------------ |
| id               | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                    |
| commentable_type | VARCHAR(255)    | NOT NULL                    | Model type (polymorphic)             |
| commentable_id   | BIGINT UNSIGNED | NOT NULL                    | Model ID (polymorphic)               |
| user_id          | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                  |
| parent_id        | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References comments.id (for replies) |
| comment          | TEXT            | NOT NULL                    | Comment text                         |
| created_at       | TIMESTAMP       | NULLABLE                    | Creation timestamp                   |
| updated_at       | TIMESTAMP       | NULLABLE                    | Last update timestamp                |
| deleted_at       | TIMESTAMP       | NULLABLE                    | Soft delete timestamp                |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (commentable_type, commentable_id)
- INDEX (user_id)
- INDEX (parent_id)

**Foreign Keys:**

- user_id → users(id) ON DELETE CASCADE
- parent_id → comments(id) ON DELETE CASCADE

---

### 16. comment_attachments

**Purpose:** File attachments for comments

| Column     | Type            | Attributes                  | Description            |
| ---------- | --------------- | --------------------------- | ---------------------- |
| id         | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier      |
| comment_id | BIGINT UNSIGNED | FOREIGN KEY                 | References comments.id |
| file_name  | VARCHAR(255)    | NOT NULL                    | Original file name     |
| file_path  | VARCHAR(255)    | NOT NULL                    | Storage path           |
| file_type  | VARCHAR(255)    | NOT NULL                    | MIME type              |
| file_size  | INT UNSIGNED    | NOT NULL                    | File size in bytes     |
| created_at | TIMESTAMP       | NOT NULL                    | Upload timestamp       |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (comment_id)

**Foreign Keys:**

- comment_id → comments(id) ON DELETE CASCADE

---

### 17. documents

**Purpose:** Polymorphic document management for multiple entity types

| Column            | Type            | Attributes                  | Description              |
| ----------------- | --------------- | --------------------------- | ------------------------ |
| id                | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier        |
| documentable_type | VARCHAR(255)    | NOT NULL                    | Model type (polymorphic) |
| documentable_id   | BIGINT UNSIGNED | NOT NULL                    | Model ID (polymorphic)   |
| title             | VARCHAR(255)    | NOT NULL                    | Document title           |
| description       | TEXT            | NULLABLE                    | Description              |
| file_name         | VARCHAR(255)    | NOT NULL                    | Original file name       |
| file_path         | VARCHAR(255)    | NOT NULL                    | Storage path             |
| file_type         | VARCHAR(255)    | NOT NULL                    | MIME type                |
| file_size         | INT UNSIGNED    | NOT NULL                    | File size in bytes       |
| uploaded_by       | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id      |
| created_at        | TIMESTAMP       | NULLABLE                    | Creation timestamp       |
| updated_at        | TIMESTAMP       | NULLABLE                    | Last update timestamp    |
| deleted_at        | TIMESTAMP       | NULLABLE                    | Soft delete timestamp    |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (documentable_type, documentable_id)
- INDEX (uploaded_by)
- INDEX (file_type)

**Foreign Keys:**

- uploaded_by → users(id) ON DELETE CASCADE

---

### 18. audit_trails

**Purpose:** Comprehensive audit logging of all data changes

| Column         | Type            | Attributes                  | Description              |
| -------------- | --------------- | --------------------------- | ------------------------ |
| id             | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier        |
| user_id        | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id      |
| action         | VARCHAR(255)    | NOT NULL                    | Action performed         |
| auditable_type | VARCHAR(255)    | NOT NULL                    | Model type (polymorphic) |
| auditable_id   | BIGINT UNSIGNED | NOT NULL                    | Model ID (polymorphic)   |
| old_values     | JSON            | NULLABLE                    | Previous values          |
| new_values     | JSON            | NULLABLE                    | New values               |
| ip_address     | VARCHAR(45)     | NULLABLE                    | User IP address          |
| user_agent     | TEXT            | NULLABLE                    | User agent string        |
| created_at     | TIMESTAMP       | NOT NULL                    | Audit timestamp          |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (auditable_type, auditable_id)
- INDEX (user_id)
- INDEX (action)
- INDEX (created_at)

**Foreign Keys:**

- user_id → users(id) ON DELETE SET NULL

---

### 19. activity_logs

**Purpose:** User activity tracking for system monitoring

| Column        | Type            | Attributes                  | Description           |
| ------------- | --------------- | --------------------------- | --------------------- |
| id            | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| user_id       | BIGINT UNSIGNED | NULLABLE, FOREIGN KEY       | References users.id   |
| activity_type | VARCHAR(255)    | NOT NULL                    | Activity type         |
| description   | TEXT            | NOT NULL                    | Activity description  |
| properties    | JSON            | NULLABLE                    | Additional properties |
| created_at    | TIMESTAMP       | NOT NULL                    | Activity timestamp    |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (activity_type)
- INDEX (created_at)
- INDEX (user_id, created_at)

**Foreign Keys:**

- user_id → users(id) ON DELETE SET NULL

---

### 20. notifications

**Purpose:** User notification system

| Column     | Type            | Attributes                  | Description           |
| ---------- | --------------- | --------------------------- | --------------------- |
| id         | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| user_id    | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id   |
| type       | VARCHAR(255)    | NOT NULL                    | Notification type     |
| title      | VARCHAR(255)    | NOT NULL                    | Notification title    |
| message    | TEXT            | NOT NULL                    | Notification message  |
| data       | JSON            | NULLABLE                    | Additional data       |
| read_at    | TIMESTAMP       | NULLABLE                    | Read timestamp        |
| created_at | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (type)
- INDEX (read_at)
- INDEX (user_id, read_at)

**Foreign Keys:**

- user_id → users(id) ON DELETE CASCADE

---

### 21. system_settings

**Purpose:** Configurable system-wide settings

| Column      | Type            | Attributes                  | Description           |
| ----------- | --------------- | --------------------------- | --------------------- |
| id          | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier     |
| key         | VARCHAR(255)    | NOT NULL, UNIQUE            | Setting key           |
| value       | TEXT            | NULLABLE                    | Setting value         |
| type        | VARCHAR(255)    | DEFAULT 'string'            | Value type            |
| description | TEXT            | NULLABLE                    | Setting description   |
| created_at  | TIMESTAMP       | NULLABLE                    | Creation timestamp    |
| updated_at  | TIMESTAMP       | NULLABLE                    | Last update timestamp |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (key)
- INDEX (key)

---

### 22. reports

**Purpose:** Track generated reports

| Column       | Type            | Attributes                  | Description                            |
| ------------ | --------------- | --------------------------- | -------------------------------------- |
| id           | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier                      |
| report_type  | VARCHAR(255)    | NOT NULL                    | Report type                            |
| title        | VARCHAR(255)    | NOT NULL                    | Report title                           |
| parameters   | JSON            | NULLABLE                    | Generation parameters                  |
| file_path    | VARCHAR(255)    | NULLABLE                    | Generated file path                    |
| generated_by | BIGINT UNSIGNED | FOREIGN KEY                 | References users.id                    |
| status       | ENUM            | DEFAULT 'pending'           | pending, processing, completed, failed |
| created_at   | TIMESTAMP       | NULLABLE                    | Creation timestamp                     |
| updated_at   | TIMESTAMP       | NULLABLE                    | Last update timestamp                  |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (report_type)
- INDEX (generated_by)
- INDEX (status)
- INDEX (created_at)

**Foreign Keys:**

- generated_by → users(id) ON DELETE CASCADE

---

## Index Strategy

### Primary Indexes

All tables have a primary key on the `id` column with AUTO_INCREMENT.

### Unique Indexes

Strategic unique indexes prevent duplicate data:

- users.email
- roles.name, roles.slug
- projects.code
- donors.name (consideration for duplicate prevention)
- expense_categories.name, expense_categories.slug
- bank_accounts.account_number
- purchase_orders.po_number
- system_settings.key
- project_donors(project_id, donor_id) - composite unique

### Performance Indexes

Indexes on frequently queried columns:

**User & Role Management:**

- users(role_id, status)
- roles(slug)

**Project & Budget Tracking:**

- projects(code, status, start_date, end_date, created_by)
- budgets(project_id, fiscal_year, status)
- budget_items(budget_id, category, cost_code)

**Financial Tracking:**

- expenses(project_id, category_id, expense_date, status, submitted_by)
- cash_flow(transaction_date, type, project_id, bank_account_id)
- purchase_orders(po_number, project_id, vendor_id, status, po_date)

**System & Audit:**

- audit_trails(auditable_type, auditable_id, user_id, action, created_at)
- activity_logs(user_id, activity_type, created_at)
- notifications(user_id, type, read_at)

### Composite Indexes

Multi-column indexes for complex queries:

- budgets(project_id, fiscal_year)
- expenses(project_id, status)
- expenses(expense_date, status)
- cash_flow(transaction_date, type)
- activity_logs(user_id, created_at)
- notifications(user_id, read_at)
- comments(commentable_type, commentable_id)
- documents(documentable_type, documentable_id)
- audit_trails(auditable_type, auditable_id)

---

## Foreign Key Relationships

### Cascade Deletion (ON DELETE CASCADE)

When parent is deleted, children are automatically deleted:

- projects → budgets, budget_items, expenses, purchase_orders, in_kind_contributions
- budgets → budget_items
- comments → comment_attachments
- users → projects (created_by), expenses (submitted_by), comments, documents, notifications

### Set Null Deletion (ON DELETE SET NULL)

When parent is deleted, foreign key is set to NULL:

- users → projects (approved_by), expenses (reviewed_by, approved_by), budgets (approved_by)
- budget_items → expenses (budget_item_id)
- projects → cash_flow (project_id)
- donors → cash_flow (donor_id)
- expenses → cash_flow (expense_id)

---

## Data Types & Constraints

### Financial Amounts

All monetary values use `DECIMAL(15,2)`:

- Precision: 15 digits
- Scale: 2 decimal places
- Range: -9,999,999,999,999.99 to 9,999,999,999,999.99

**Why DECIMAL?**

- Exact precision (no floating-point errors)
- Required for financial calculations
- Prevents rounding errors in currency operations

### Date & Time Fields

- `DATE`: expense_date, po_date, transaction_date, contribution_date
- `TIMESTAMP`: created_at, updated_at, deleted_at, approved_at, reviewed_at, submitted_at

### ENUM Fields

Predefined value lists:

**users.status:**

- active
- inactive
- suspended

**projects.status:**

- planning
- active
- on_hold
- completed
- cancelled

**budgets.status:**

- draft
- submitted
- approved
- rejected

**expenses.status:**

- draft
- submitted
- under_review
- approved
- rejected
- paid

**purchase_orders.status:**

- draft
- pending
- approved
- rejected
- completed
- cancelled

**cash_flow.type:**

- cash_in
- cash_out

**reports.status:**

- pending
- processing
- completed
- failed

### JSON Fields

Flexible data storage:

- audit_trails(old_values, new_values)
- activity_logs(properties)
- notifications(data)
- reports(parameters)

### Text Fields

- TEXT: Long content (descriptions, comments, addresses)
- VARCHAR(255): Standard strings (names, titles, file names)
- VARCHAR(100): Tokens (remember_token)
- VARCHAR(45): IP addresses (supports IPv6)
- VARCHAR(3): Currency codes (USD, EUR, etc.)

---

## Seeded Data

### Roles (3 records)

| ID  | Name             | Slug             | Description                             |
| --- | ---------------- | ---------------- | --------------------------------------- |
| 1   | Programs Manager | programs-manager | Highest authority - Full system access  |
| 2   | Finance Officer  | finance-officer  | Middle authority - Financial operations |
| 3   | Project Officer  | project-officer  | Base authority - Project implementation |

### Expense Categories (5 records)

| ID  | Name                    | Slug                    | Description                              |
| --- | ----------------------- | ----------------------- | ---------------------------------------- |
| 1   | Travel                  | travel                  | Travel expenses including transportation |
| 2   | Staff Salaries          | staff-salaries          | Employee salaries and wages              |
| 3   | Procurement/Supplies    | procurement-supplies    | Office supplies and equipment            |
| 4   | Consultants/Contractors | consultants-contractors | External consultants and contractors     |
| 5   | Other                   | other                   | Miscellaneous expenses                   |

### Admin User (1 record)

| Field           | Value                            |
| --------------- | -------------------------------- |
| Name            | CANZIM Administrator             |
| Email           | admin@canzim.org.zw              |
| Password        | canzim@2025 (hashed with bcrypt) |
| Role            | Programs Manager                 |
| Office Location | Head Office                      |
| Status          | active                           |

### System Settings (11 records)

| Key                       | Value                           | Type    | Description               |
| ------------------------- | ------------------------------- | ------- | ------------------------- |
| org_name                  | Climate Action Network Zimbabwe | string  | Organization name         |
| org_short_name            | CANZIM                          | string  | Organization acronym      |
| org_logo                  | /images/logo/canzim_logo.png    | string  | Logo path                 |
| currency                  | USD                             | string  | Default currency          |
| timezone                  | Africa/Harare                   | string  | System timezone           |
| session_timeout           | 5                               | integer | Session timeout (minutes) |
| date_format               | d/m/Y                           | string  | Date format (PHP)         |
| datetime_format           | d/m/Y H:i                       | string  | Datetime format (PHP)     |
| max_file_size_documents   | 5120                            | integer | Max document size (KB)    |
| max_file_size_receipts    | 5120                            | integer | Max receipt size (KB)     |
| max_file_size_attachments | 2048                            | integer | Max attachment size (KB)  |

---

## Database Maintenance

### Backup Strategy

```bash
# Full database backup
mysqldump -u root -p my_canzimdb > backup_$(date +%Y%m%d).sql

# Backup with compression
mysqldump -u root -p my_canzimdb | gzip > backup_$(date +%Y%m%d).sql.gz

# Restore from backup
mysql -u root -p my_canzimdb < backup_20251114.sql
```

### Optimization

```bash
# Analyze tables
php artisan db:analyze

# Optimize tables manually
ANALYZE TABLE users, projects, expenses, budgets;
OPTIMIZE TABLE users, projects, expenses, budgets;
```

### Testing Database

```bash
# Use testing database
php artisan migrate --database=testing

# Run tests with database
php artisan test --filter=Database
```

---

**End of Schema Documentation**
