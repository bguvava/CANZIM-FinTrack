# Document Management Module - Overview

## Table of Contents

1. [Introduction](#introduction)
2. [Module Purpose](#module-purpose)
3. [Key Features](#key-features)
4. [Architecture](#architecture)
5. [Database Schema](#database-schema)
6. [Access Control](#access-control)
7. [File Types & Validation](#file-types--validation)
8. [Version Management](#version-management)

## Introduction

The Document Management module provides a comprehensive solution for organizing, storing, and managing documents related to projects, budgets, expenses, and donors within the CANZIM FinTrack system. It implements polymorphic relationships, version control, category-based organization, and role-based access control.

**Module ID:** Module 12  
**Requirements:** REQ-588 to REQ-635 (48 requirements)  
**Status:** ✅ Complete - 100% Pass Rate (31/31 tests passed)

## Module Purpose

The Document Management module serves as the centralized document repository for the CANZIM FinTrack system, enabling:

1. **Polymorphic Document Storage** - Documents can be attached to Projects, Budgets, Expenses, or Donors
2. **Version Control** - Track document history with automatic version numbering
3. **Category-Based Organization** - Organize documents using predefined categories
4. **Role-Based Access Control** - Secure document access based on user roles and ownership
5. **Search & Filtering** - Powerful search capabilities with multiple filter options
6. **Activity Logging** - Complete audit trail of all document operations

## Key Features

### ✅ Document Upload

- Support for multiple file types: PDF, Word, Excel, Images (JPG, PNG)
- Maximum file size: 5MB per document
- Automatic UUID-based filename generation
- Organized folder structure: `/documents/{YYYY}/{MM}/`
- Metadata storage: title, description, category

### ✅ Version Management

- Automatic version numbering (1, 2, 3, ...)
- Archive old versions to `/documents/archive/{YYYY}/{MM}/`
- Track version history with timestamps and user info
- View complete version timeline
- Ability to replace current document with new version

### ✅ Document Categories

Five predefined categories:

1. **Budget Documents** (`budget-documents`) - Budget proposals, allocations
2. **Expense Receipts** (`expense-receipts`) - Receipts, invoices for expenses
3. **Project Reports** (`project-reports`) - Project deliverables, reports
4. **Donor Agreements** (`donor-agreements`) - Contracts, MOUs with donors
5. **Other** (`other`) - Miscellaneous documents

### ✅ Search & Filtering

- **Search:** Full-text search across title, description, filename
- **Filter by Category:** Budget documents, expense receipts, etc.
- **Filter by File Type:** PDF, Word, Excel, Images
- **Filter by Date Range:** Upload date filtering
- **Sort Options:** Created date, title, file size
- **Maximum 5 filters** - Optimized for database performance

### ✅ Access Control

- **Owner Access** - Full control over documents you uploaded
- **Project-Based Access** - Access documents for projects you created
- **Programs Manager Override** - Full access to all documents
- **Finance Officer Access** - Access to budget and expense documents
- **Unauthenticated Blocking** - No anonymous access

### ✅ Document Operations

- **View** - Open document in new browser tab (inline viewing)
- **Download** - Download document file
- **Update Metadata** - Change title, description, category
- **Delete** - Soft delete with activity logging
- **Replace** - Upload new version and archive old one

## Architecture

### Backend Stack

```
┌─────────────────────────────────────────────────────────┐
│                   API Routes                             │
│             /api/v1/documents/*                          │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│              DocumentController                          │
│  index() store() show() update() destroy()              │
│  download() replace() versions() categories()           │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│               DocumentService                            │
│  uploadDocument() replaceDocument()                      │
│  updateMetadata() deleteDocument()                       │
└─────────────────────────────────────────────────────────┘
                        ↓
┌──────────────────┬──────────────────┬──────────────────┐
│  Document Model  │ DocumentVersion  │ DocumentCategory │
│  (Polymorphic)   │    (History)     │  (Categories)    │
└──────────────────┴──────────────────┴──────────────────┘
```

### Authorization Flow

```
Request → Auth Middleware → Policy Check → Controller → Service → Storage
                              ↓
                        DocumentPolicy
                        - viewAny()
                        - view()
                        - create()
                        - update()
                        - delete()
                        - download()
                        - replace()
```

## Database Schema

### 1. `documents` Table

Stores document metadata with polymorphic relationships.

| Column               | Type                       | Description                                        |
| -------------------- | -------------------------- | -------------------------------------------------- |
| `id`                 | BIGINT UNSIGNED            | Primary key                                        |
| `documentable_type`  | VARCHAR(255)               | Polymorphic type (Project, Budget, Expense, Donor) |
| `documentable_id`    | BIGINT UNSIGNED            | Polymorphic ID                                     |
| `title`              | VARCHAR(255)               | Document title                                     |
| `description`        | TEXT (nullable)            | Document description                               |
| `category`           | VARCHAR(100)               | Category slug                                      |
| `file_name`          | VARCHAR(255)               | Original filename                                  |
| `file_path`          | VARCHAR(500)               | Storage path                                       |
| `file_type`          | VARCHAR(100)               | MIME type                                          |
| `file_size`          | INTEGER                    | File size in bytes                                 |
| `uploaded_by`        | BIGINT UNSIGNED            | User who uploaded                                  |
| `version_number`     | INTEGER                    | Current version (1, 2, 3...)                       |
| `current_version_id` | BIGINT UNSIGNED (nullable) | Link to current version                            |
| `created_at`         | TIMESTAMP                  | Upload timestamp                                   |
| `updated_at`         | TIMESTAMP                  | Last update timestamp                              |
| `deleted_at`         | TIMESTAMP (nullable)       | Soft delete timestamp                              |

**Indexes:**

- `documentable_type` + `documentable_id` (compound)
- `uploaded_by`
- `category`
- `created_at`

### 2. `document_versions` Table

Stores version history for documents.

| Column           | Type                       | Description              |
| ---------------- | -------------------------- | ------------------------ |
| `id`             | BIGINT UNSIGNED            | Primary key              |
| `document_id`    | BIGINT UNSIGNED            | Foreign key to documents |
| `version_number` | INTEGER                    | Version number           |
| `file_name`      | VARCHAR(255)               | Archived filename        |
| `file_path`      | VARCHAR(500)               | Archive path             |
| `file_type`      | VARCHAR(100)               | MIME type                |
| `file_size`      | INTEGER                    | File size in bytes       |
| `replaced_by`    | BIGINT UNSIGNED (nullable) | User who replaced        |
| `replaced_at`    | TIMESTAMP (nullable)       | Replacement timestamp    |
| `created_at`     | TIMESTAMP                  | Version creation time    |

**Indexes:**

- `document_id` + `version_number` (compound unique)
- `replaced_by`

### 3. `document_categories` Table

Stores available document categories.

| Column          | Type            | Description             |
| --------------- | --------------- | ----------------------- |
| `id`            | BIGINT UNSIGNED | Primary key             |
| `name`          | VARCHAR(255)    | Category name           |
| `slug`          | VARCHAR(100)    | URL-friendly identifier |
| `description`   | TEXT (nullable) | Category description    |
| `is_active`     | BOOLEAN         | Active status           |
| `display_order` | INTEGER         | Sort order              |
| `created_at`    | TIMESTAMP       | Creation timestamp      |
| `updated_at`    | TIMESTAMP       | Last update timestamp   |

**Seeded Categories:**

1. Budget Documents
2. Expense Receipts
3. Project Reports
4. Donor Agreements
5. Other

## Access Control

### Role-Based Permissions

#### Programs Manager

- ✅ Full access to ALL documents
- ✅ Can view any document
- ✅ Can update any document
- ✅ Can delete any document
- ✅ Can download any document
- ✅ Can replace any document

#### Finance Officer

- ✅ Access to budget and expense documents
- ✅ Can view documents for budgets/expenses they manage
- ✅ Cannot access project reports unless creator

#### Project Officer

- ✅ Access to documents they uploaded
- ✅ Access to documents for projects they created
- ✅ Can update/delete own documents only
- ❌ Cannot access documents from other users

### Document Access Logic

Documents use **documentable ownership** for access control:

```php
// For Project Documents
if (documentable_type === Project) {
    access = (project.created_by === user_id) OR (user.role === programs-manager)
}

// For Budget Documents
if (documentable_type === Budget) {
    access = (budget.created_by === user_id) OR (user.role IN [programs-manager, finance-officer])
}

// For Expense Documents
if (documentable_type === Expense) {
    access = (expense.submitted_by === user_id) OR (user.role IN [programs-manager, finance-officer])
}

// For Donor Documents
if (documentable_type === Donor) {
    access = authenticated
}
```

## File Types & Validation

### Supported File Types

| Type   | Extensions              | MIME Types                                                                                      | Max Size |
| ------ | ----------------------- | ----------------------------------------------------------------------------------------------- | -------- |
| PDF    | `.pdf`                  | `application/pdf`                                                                               | 5MB      |
| Word   | `.doc`, `.docx`         | `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | 5MB      |
| Excel  | `.xls`, `.xlsx`         | `application/vnd.ms-excel`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` | 5MB      |
| Images | `.jpg`, `.jpeg`, `.png` | `image/jpeg`, `image/png`                                                                       | 5MB      |

### Validation Rules

**Store Document:**

```php
[
    'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'category' => 'required|string|exists:document_categories,slug',
    'documentable_type' => 'required|string|in:App\Models\Project,App\Models\Budget,App\Models\Expense,App\Models\Donor',
    'documentable_id' => 'required|integer|exists:[documentable_type],id',
]
```

**Update Document:**

```php
[
    'title' => 'sometimes|string|max:255',
    'description' => 'nullable|string',
    'category' => 'sometimes|string|exists:document_categories,slug',
]
```

### File Storage Structure

```
storage/app/public/documents/
├── 2025/
│   ├── 01/
│   │   ├── uuid-filename.pdf
│   │   └── uuid-filename.docx
│   ├── 02/
│   └── .../
├── archive/
│   └── 2025/
│       ├── 01/
│       │   ├── old-uuid-filename-v1.pdf
│       │   └── old-uuid-filename-v2.pdf
│       └── .../
```

## Version Management

### Version Lifecycle

1. **Initial Upload** (Version 1)
    - File stored in `/documents/{YYYY}/{MM}/`
    - `version_number` = 1
    - No version record created yet

2. **First Replacement** (Version 2)
    - Old file moved to `/documents/archive/{YYYY}/{MM}/`
    - New file stored in `/documents/{YYYY}/{MM}/`
    - Version record created for v1 with archive path
    - `version_number` = 2
    - `current_version_id` points to v2 record

3. **Subsequent Replacements** (Version 3+)
    - Previous version archived
    - New version record created
    - `version_number` incremented
    - `current_version_id` updated

### Version History

Accessing version history:

```http
GET /api/v1/documents/{id}/versions
```

Response includes:

- Version number
- File name and size
- User who replaced it
- Replacement timestamp
- Archive file path

### Version Attributes

Each version record maintains:

- Original file metadata (name, size, type)
- Archive storage path
- Replacement timestamp
- User who performed replacement

---

## Next Steps

For implementation details, API endpoints, and usage examples, see:

- [API Documentation](./api-endpoints.md)
- [Usage Guide](./usage-guide.md)

---

**Module Status:** ✅ Complete  
**Last Updated:** November 20, 2025  
**Developed by:** bguvava (https://bguvava.com)
