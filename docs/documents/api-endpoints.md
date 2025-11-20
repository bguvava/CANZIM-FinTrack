# Document Management API - Endpoints

## Base URL

```
/api/v1/documents
```

## Authentication

All endpoints require authentication via Laravel Sanctum tokens.

**Headers:**

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data (for file uploads)
Content-Type: application/json (for other requests)
```

---

## Endpoints Overview

| Method    | Endpoint                   | Description                   | Access        |
| --------- | -------------------------- | ----------------------------- | ------------- |
| GET       | `/documents`               | List all accessible documents | Authenticated |
| POST      | `/documents`               | Upload a new document         | Authenticated |
| GET       | `/documents/{id}`          | Get document details          | Owner / PM    |
| PUT/PATCH | `/documents/{id}`          | Update document metadata      | Owner / PM    |
| DELETE    | `/documents/{id}`          | Delete document               | Owner / PM    |
| GET       | `/documents/{id}/download` | Download document file        | Owner / PM    |
| POST      | `/documents/{id}/replace`  | Replace with new version      | Owner / PM    |
| GET       | `/documents/{id}/versions` | Get version history           | Owner / PM    |
| GET       | `/documents/categories`    | Get all categories            | Authenticated |

**PM** = Programs Manager

---

## 1. List Documents

Get all documents the authenticated user has access to.

### Request

```http
GET /api/v1/documents
```

### Query Parameters

| Parameter           | Type    | Required | Description                              |
| ------------------- | ------- | -------- | ---------------------------------------- |
| `search`            | string  | No       | Search in title, description, filename   |
| `category`          | string  | No       | Filter by category slug                  |
| `file_type`         | string  | No       | Filter by MIME type                      |
| `documentable_type` | string  | No       | Filter by entity type                    |
| `documentable_id`   | integer | No       | Filter by entity ID                      |
| `from_date`         | date    | No       | Filter by upload date (start)            |
| `to_date`           | date    | No       | Filter by upload date (end)              |
| `sort_by`           | string  | No       | Sort field (default: created_at)         |
| `sort_order`        | string  | No       | Sort direction (asc/desc, default: desc) |

### Example Request

```bash
curl -X GET "http://localhost/api/v1/documents?search=budget&category=budget-documents&sort_by=title" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "documentable_type": "App\\Models\\Budget",
            "documentable_id": 5,
            "title": "Q1 2025 Budget Proposal",
            "description": "Quarterly budget allocation for education program",
            "category": "budget-documents",
            "file_name": "budget-q1-2025.pdf",
            "file_path": "documents/2025/01/uuid-budget-q1-2025.pdf",
            "file_type": "application/pdf",
            "file_size": 524288,
            "uploaded_by": 3,
            "version_number": 1,
            "created_at": "2025-01-15T10:30:00.000000Z",
            "updated_at": "2025-01-15T10:30:00.000000Z",
            "uploader": {
                "id": 3,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "documentable": {
                "id": 5,
                "title": "Education Program Budget",
                "fiscal_year": 2025
            }
        }
    ],
    "meta": {
        "total": 1,
        "filters_applied": {
            "search": "budget",
            "category": "budget-documents"
        }
    }
}
```

---

## 2. Upload Document

Upload a new document and attach it to a project, budget, expense, or donor.

### Request

```http
POST /api/v1/documents
Content-Type: multipart/form-data
```

### Form Data

| Field               | Type    | Required | Description                              |
| ------------------- | ------- | -------- | ---------------------------------------- |
| `file`              | file    | Yes      | Document file (max 5MB)                  |
| `title`             | string  | Yes      | Document title (max 255 chars)           |
| `description`       | string  | No       | Document description                     |
| `category`          | string  | Yes      | Category slug (from categories endpoint) |
| `documentable_type` | string  | Yes      | Entity type (App\Models\Project, etc.)   |
| `documentable_id`   | integer | Yes      | Entity ID                                |

### Example Request

```bash
curl -X POST "http://localhost/api/v1/documents" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "file=@/path/to/budget.pdf" \
  -F "title=Q1 Budget Proposal" \
  -F "description=Quarterly budget for education program" \
  -F "category=budget-documents" \
  -F "documentable_type=App\Models\Budget" \
  -F "documentable_id=5"
```

### Success Response (201 Created)

```json
{
    "success": true,
    "message": "Document uploaded successfully",
    "data": {
        "id": 1,
        "documentable_type": "App\\Models\\Budget",
        "documentable_id": 5,
        "title": "Q1 Budget Proposal",
        "description": "Quarterly budget for education program",
        "category": "budget-documents",
        "file_name": "budget.pdf",
        "file_path": "documents/2025/01/550e8400-e29b-41d4-a716-446655440000-budget.pdf",
        "file_type": "application/pdf",
        "file_size": 524288,
        "uploaded_by": 3,
        "version_number": 1,
        "created_at": "2025-01-15T10:30:00.000000Z",
        "updated_at": "2025-01-15T10:30:00.000000Z"
    }
}
```

### Error Responses

**Validation Error (422 Unprocessable Entity)**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "file": [
            "The file must be a file of type: pdf, doc, docx, xls, xlsx, jpg, jpeg, png."
        ],
        "title": ["The title field is required."],
        "category": ["The selected category is invalid."]
    }
}
```

**File Too Large (422)**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "file": ["The file must not be greater than 5120 kilobytes."]
    }
}
```

---

## 3. Get Document Details

Retrieve detailed information about a specific document.

### Request

```http
GET /api/v1/documents/{id}
```

### Example Request

```bash
curl -X GET "http://localhost/api/v1/documents/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "documentable_type": "App\\Models\\Project",
        "documentable_id": 10,
        "title": "Project Implementation Plan",
        "description": "Detailed implementation roadmap for Q1 2025",
        "category": "project-reports",
        "file_name": "implementation-plan.pdf",
        "file_path": "documents/2025/01/uuid-implementation-plan.pdf",
        "file_type": "application/pdf",
        "file_size": 1048576,
        "uploaded_by": 2,
        "version_number": 2,
        "current_version_id": 5,
        "created_at": "2025-01-10T14:20:00.000000Z",
        "updated_at": "2025-01-15T16:45:00.000000Z",
        "uploader": {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com"
        },
        "documentable": {
            "id": 10,
            "name": "Education Infrastructure Project",
            "status": "active"
        }
    }
}
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to access this document"
}
```

### Error Response (404 Not Found)

```json
{
    "success": false,
    "message": "Document not found"
}
```

---

## 4. Update Document Metadata

Update title, description, or category of an existing document.

### Request

```http
PUT /api/v1/documents/{id}
PATCH /api/v1/documents/{id}
Content-Type: application/json
```

### Request Body

| Field         | Type   | Required | Description              |
| ------------- | ------ | -------- | ------------------------ |
| `title`       | string | No       | New document title       |
| `description` | string | No       | New document description |
| `category`    | string | No       | New category slug        |

### Example Request

```bash
curl -X PUT "http://localhost/api/v1/documents/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Implementation Plan",
    "description": "Revised roadmap with stakeholder feedback",
    "category": "project-reports"
  }'
```

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Document updated successfully",
    "data": {
        "id": 1,
        "title": "Updated Implementation Plan",
        "description": "Revised roadmap with stakeholder feedback",
        "category": "project-reports",
        "updated_at": "2025-01-16T09:15:00.000000Z"
    }
}
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to update this document"
}
```

---

## 5. Delete Document

Soft delete a document and its file.

### Request

```http
DELETE /api/v1/documents/{id}
```

### Example Request

```bash
curl -X DELETE "http://localhost/api/v1/documents/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Document deleted successfully"
}
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to delete this document"
}
```

---

## 6. Download Document

Download the document file with proper headers for browser handling.

### Request

```http
GET /api/v1/documents/{id}/download
```

### Example Request

```bash
curl -X GET "http://localhost/api/v1/documents/1/download" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -o downloaded-file.pdf
```

### Success Response (200 OK)

```http
HTTP/1.1 200 OK
Content-Type: application/pdf
Content-Disposition: attachment; filename="implementation-plan.pdf"
Content-Length: 1048576
Cache-Control: no-cache, must-revalidate

[Binary file content]
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to download this document"
}
```

### Error Response (404 Not Found)

```json
{
    "success": false,
    "message": "Document file not found on server"
}
```

---

## 7. Replace Document (Create New Version)

Upload a new file to replace the current document, creating a new version.

### Request

```http
POST /api/v1/documents/{id}/replace
Content-Type: multipart/form-data
```

### Form Data

| Field  | Type | Required | Description                 |
| ------ | ---- | -------- | --------------------------- |
| `file` | file | Yes      | New document file (max 5MB) |

### Example Request

```bash
curl -X POST "http://localhost/api/v1/documents/1/replace" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "file=@/path/to/updated-budget.pdf"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Document replaced successfully",
    "data": {
        "id": 1,
        "file_name": "updated-budget.pdf",
        "file_path": "documents/2025/01/new-uuid-updated-budget.pdf",
        "file_type": "application/pdf",
        "file_size": 612352,
        "version_number": 3,
        "current_version_id": 7,
        "updated_at": "2025-01-16T11:30:00.000000Z",
        "previous_version": {
            "version_number": 2,
            "file_name": "budget-q1-2025.pdf",
            "archived_path": "documents/archive/2025/01/old-uuid-budget-q1-2025.pdf",
            "replaced_at": "2025-01-16T11:30:00.000000Z"
        }
    }
}
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to replace this document"
}
```

### Error Response (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "file": [
            "The file must be a file of type: pdf, doc, docx, xls, xlsx, jpg, jpeg, png."
        ]
    }
}
```

---

## 8. Get Version History

Retrieve all versions of a document.

### Request

```http
GET /api/v1/documents/{id}/versions
```

### Example Request

```bash
curl -X GET "http://localhost/api/v1/documents/1/versions" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "data": {
        "document_id": 1,
        "current_version": 3,
        "versions": [
            {
                "id": 7,
                "version_number": 3,
                "file_name": "updated-budget.pdf",
                "file_path": "documents/2025/01/new-uuid-updated-budget.pdf",
                "file_type": "application/pdf",
                "file_size": 612352,
                "is_current": true,
                "created_at": "2025-01-16T11:30:00.000000Z"
            },
            {
                "id": 5,
                "version_number": 2,
                "file_name": "budget-q1-2025.pdf",
                "file_path": "documents/archive/2025/01/old-uuid-budget-q1-2025.pdf",
                "file_type": "application/pdf",
                "file_size": 524288,
                "is_current": false,
                "replaced_by": 3,
                "replaced_at": "2025-01-16T11:30:00.000000Z",
                "replacer": {
                    "id": 3,
                    "name": "John Doe"
                }
            },
            {
                "id": 2,
                "version_number": 1,
                "file_name": "initial-budget.pdf",
                "file_path": "documents/archive/2025/01/original-uuid-initial-budget.pdf",
                "file_type": "application/pdf",
                "file_size": 450560,
                "is_current": false,
                "replaced_by": 3,
                "replaced_at": "2025-01-15T14:20:00.000000Z",
                "replacer": {
                    "id": 3,
                    "name": "John Doe"
                }
            }
        ]
    }
}
```

### Error Response (403 Forbidden)

```json
{
    "success": false,
    "message": "Unauthorized to view document versions"
}
```

---

## 9. Get Document Categories

Retrieve all available document categories.

### Request

```http
GET /api/v1/documents/categories
```

### Example Request

```bash
curl -X GET "http://localhost/api/v1/documents/categories" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Budget Documents",
            "slug": "budget-documents",
            "description": "Budget proposals, allocations, and financial planning documents",
            "is_active": true,
            "display_order": 1
        },
        {
            "id": 2,
            "name": "Expense Receipts",
            "slug": "expense-receipts",
            "description": "Receipts, invoices, and proof of expenses",
            "is_active": true,
            "display_order": 2
        },
        {
            "id": 3,
            "name": "Project Reports",
            "slug": "project-reports",
            "description": "Project deliverables, progress reports, and documentation",
            "is_active": true,
            "display_order": 3
        },
        {
            "id": 4,
            "name": "Donor Agreements",
            "slug": "donor-agreements",
            "description": "Contracts, MOUs, and agreements with donors",
            "is_active": true,
            "display_order": 4
        },
        {
            "id": 5,
            "name": "Other",
            "slug": "other",
            "description": "Miscellaneous documents",
            "is_active": true,
            "display_order": 5
        }
    ]
}
```

---

## Error Handling

### Standard Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### HTTP Status Codes

| Code | Meaning               | Common Scenarios                  |
| ---- | --------------------- | --------------------------------- |
| 200  | OK                    | Successful GET, PUT, DELETE       |
| 201  | Created               | Successful POST (document upload) |
| 400  | Bad Request           | Malformed request                 |
| 401  | Unauthorized          | Missing or invalid auth token     |
| 403  | Forbidden             | User lacks permission             |
| 404  | Not Found             | Document doesn't exist            |
| 422  | Unprocessable Entity  | Validation errors                 |
| 500  | Internal Server Error | Server-side error                 |

---

## Activity Logging

All document operations are automatically logged to the `activity_logs` table:

| Operation | Activity Type       | Properties Logged           |
| --------- | ------------------- | --------------------------- |
| Upload    | `document_upload`   | document_id, file_name      |
| Update    | `document_update`   | document_id                 |
| Delete    | `document_delete`   | document_id, file_name      |
| Download  | `document_download` | document_id, file_name      |
| Replace   | `document_replace`  | document_id, version_number |

Access logs via Activity Log module endpoints.

---

## Testing with cURL

### Complete Upload Example

```bash
# 1. Get auth token (assuming login endpoint exists)
TOKEN=$(curl -X POST "http://localhost/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Upload document
curl -X POST "http://localhost/api/v1/documents" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "file=@./budget.pdf" \
  -F "title=Q1 2025 Budget" \
  -F "description=Budget proposal" \
  -F "category=budget-documents" \
  -F "documentable_type=App\Models\Budget" \
  -F "documentable_id=1"

# 3. List documents
curl -X GET "http://localhost/api/v1/documents?category=budget-documents" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 4. Download document
curl -X GET "http://localhost/api/v1/documents/1/download" \
  -H "Authorization: Bearer $TOKEN" \
  -o downloaded.pdf
```

---

## Rate Limiting

API endpoints are subject to rate limiting:

- **Authenticated Requests:** 60 requests per minute
- **Unauthenticated Requests:** 10 requests per minute (not applicable for these endpoints)

Rate limit headers:

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1642165200
```

---

**Last Updated:** November 20, 2025  
**API Version:** 1.0
