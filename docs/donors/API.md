# Donor Management API Documentation

**Base URL:** `/api/v1/donors`

**Authentication:** Laravel Sanctum (Bearer Token)

**Authorization:** Role-based access control via DonorPolicy

---

## Endpoints

### 1. List All Donors

**Endpoint:** `GET /api/v1/donors`

**Permission:** Programs Manager, Finance Officer

**Query Parameters:**

- `page` (integer, default: 1) - Page number
- `per_page` (integer, default: 25) - Items per page
- `search` (string) - Search by name, email, or contact person
- `status` (string) - Filter by status (`active`, `inactive`)
- `funding_min` (decimal) - Minimum total funding amount

**Response:**

```json
{
    "success": true,
    "message": "Donors retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Global Climate Fund",
                "contact_person": "Dr. Sarah Mitchell",
                "email": "info@globalclimatefund.org",
                "phone": "+1 555-0101",
                "address": "123 Climate Ave, Geneva, Switzerland",
                "tax_id": "CH-123456789",
                "website": "https://globalclimatefund.org",
                "status": "active",
                "notes": "Major international climate funding organization",
                "total_funding": "2500000.00",
                "restricted_funding": "1500000.00",
                "unrestricted_funding": "1000000.00",
                "in_kind_total": "75000.00",
                "active_projects_count": 3,
                "total_projects_count": 5,
                "last_contribution_date": "2025-10-15",
                "created_at": "2025-11-17T07:45:00Z",
                "updated_at": "2025-11-17T07:45:00Z"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 25,
        "total": 24
    }
}
```

---

### 2. Get Donor Statistics

**Endpoint:** `GET /api/v1/donors/statistics`

**Permission:** Programs Manager, Finance Officer

**Response:**

```json
{
    "success": true,
    "data": {
        "total_donors": 24,
        "active_donors": 20,
        "total_funding": "15750000.00",
        "average_funding": "656250.00"
    }
}
```

---

### 3. Create Donor

**Endpoint:** `POST /api/v1/donors`

**Permission:** Programs Manager only

**Request Body:**

```json
{
    "name": "New Foundation",
    "contact_person": "John Doe",
    "email": "contact@newfoundation.org",
    "phone": "+263 XX XXX XXXX",
    "address": "123 Main St, Harare",
    "tax_id": "ZW-987654321",
    "website": "https://newfoundation.org",
    "status": "active",
    "notes": "Interested in renewable energy projects"
}
```

**Validation Rules:**

- `name`: required, string, max:255
- `email`: nullable, email, unique:donors,email
- `phone`: nullable, string, max:20
- `address`: nullable, string
- `contact_person`: nullable, string, max:255
- `tax_id`: nullable, string, max:100
- `website`: nullable, url, max:255
- `status`: required, in:active,inactive
- `notes`: nullable, string

**Response:**

```json
{
    "success": true,
    "message": "Donor created successfully",
    "data": {
        /* Donor object */
    }
}
```

---

### 4. View Donor

**Endpoint:** `GET /api/v1/donors/{donor}`

**Permission:** Programs Manager, Finance Officer

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Global Climate Fund",
        /* ... other donor fields ... */
        "projects": [
            {
                "id": 1,
                "name": "Solar Irrigation Project",
                "code": "PROJ-001",
                "pivot": {
                    "funding_amount": "500000.00",
                    "funding_start_date": "2025-01-01",
                    "funding_end_date": "2025-12-31",
                    "is_restricted": true,
                    "notes": "For equipment purchase only"
                }
            }
        ],
        "in_kind_contributions": [
            {
                "id": 1,
                "description": "Solar panel installation equipment",
                "estimated_value": "25000.00",
                "contribution_date": "2025-03-15",
                "category": "equipment"
            }
        ],
        "communications": [
            {
                "id": 1,
                "type": "email",
                "subject": "Q3 Project Update",
                "communication_date": "2025-09-15T10:30:00Z",
                "notes": "Discussed project progress and future funding opportunities",
                "next_action_date": "2025-12-01",
                "next_action_notes": "Schedule site visit"
            }
        ]
    }
}
```

---

### 5. Update Donor

**Endpoint:** `PUT /api/v1/donors/{donor}`

**Permission:** Programs Manager only

**Request Body:** Same as Create Donor (all fields optional)

**Validation:** Same as create, except email unique rule excludes current donor

**Response:**

```json
{
    "success": true,
    "message": "Donor updated successfully",
    "data": {
        /* Updated donor object */
    }
}
```

---

### 6. Delete Donor

**Endpoint:** `DELETE /api/v1/donors/{donor}`

**Permission:** Programs Manager only

**Validation:** Cannot delete donor with active project assignments

**Response:**

```json
{
    "success": true,
    "message": "Donor deleted successfully"
}
```

**Error Response (422):**

```json
{
    "success": false,
    "message": "Cannot delete donor with active project assignments"
}
```

---

### 7. Toggle Donor Status

**Endpoint:** `POST /api/v1/donors/{donor}/toggle-status`

**Permission:** Programs Manager only

**Response:**

```json
{
    "success": true,
    "message": "Donor status updated successfully",
    "data": {
        /* Updated donor object */
    }
}
```

---

### 8. Assign Donor to Project

**Endpoint:** `POST /api/v1/donors/{donor}/assign-project`

**Permission:** Programs Manager only

**Request Body:**

```json
{
    "project_id": 1,
    "funding_amount": 500000.0,
    "funding_start_date": "2025-01-01",
    "funding_end_date": "2025-12-31",
    "is_restricted": true,
    "notes": "Restricted to equipment purchase only"
}
```

**Validation Rules:**

- `project_id`: required, exists:projects,id
- `funding_amount`: required, numeric, min:0.01
- `funding_start_date`: required, date
- `funding_end_date`: required, date, after:funding_start_date
- `is_restricted`: required, boolean
- `notes`: nullable, string

**Response:**

```json
{
    "success": true,
    "message": "Donor assigned to project successfully",
    "data": {
        /* Project object with pivot data */
    }
}
```

---

### 9. Remove Donor from Project

**Endpoint:** `DELETE /api/v1/donors/{donor}/projects/{project}`

**Permission:** Programs Manager only

**Validation:** Cannot remove if project has expenses funded by this donor

**Response:**

```json
{
    "success": true,
    "message": "Donor removed from project successfully"
}
```

**Error Response (422):**

```json
{
    "success": false,
    "message": "Cannot remove donor: Project has expenses funded by this donor"
}
```

---

### 10. Get Funding Summary

**Endpoint:** `GET /api/v1/donors/{donor}/funding-summary`

**Permission:** Programs Manager, Finance Officer

**Response:**

```json
{
    "success": true,
    "data": {
        "total_funding": "2500000.00",
        "restricted_funding": "1500000.00",
        "unrestricted_funding": "1000000.00",
        "in_kind_total": "75000.00",
        "active_projects": 3,
        "total_projects": 5,
        "last_contribution_date": "2025-10-15"
    }
}
```

---

### 11. Add In-Kind Contribution

**Endpoint:** `POST /api/v1/in-kind-contributions`

**Permission:** Programs Manager only

**Request Body:**

```json
{
    "donor_id": 1,
    "project_id": 1,
    "description": "Solar panel installation equipment",
    "estimated_value": 25000.0,
    "contribution_date": "2025-03-15",
    "category": "equipment"
}
```

**Validation Rules:**

- `donor_id`: required, exists:donors,id
- `project_id`: required, exists:projects,id
- `description`: required, string, max:500
- `estimated_value`: required, numeric, min:0.01
- `contribution_date`: required, date
- `category`: required, in:equipment,supplies,services,training,other

**Response:**

```json
{
    "success": true,
    "message": "In-kind contribution recorded successfully",
    "data": {
        /* In-kind contribution object */
    }
}
```

---

### 12. Log Communication

**Endpoint:** `POST /api/v1/communications`

**Permission:** Programs Manager only

**Content-Type:** `multipart/form-data` (for file upload)

**Request Body:**

```json
{
    "communicable_type": "App\\Models\\Donor",
    "communicable_id": 1,
    "type": "email",
    "communication_date": "2025-09-15 10:30:00",
    "subject": "Q3 Project Update",
    "notes": "Discussed project progress and future funding opportunities",
    "attachment": /* File upload (max 5MB) */,
    "next_action_date": "2025-12-01",
    "next_action_notes": "Schedule site visit"
}
```

**Validation Rules:**

- `communicable_type`: required, in:App\Models\Donor
- `communicable_id`: required, integer
- `type`: required, in:email,phone_call,meeting,letter,other
- `communication_date`: required, date
- `subject`: required, string, max:255
- `notes`: nullable, string
- `attachment`: nullable, file, max:5120, mimes:pdf,doc,docx,jpg,jpeg,png
- `next_action_date`: nullable, date
- `next_action_notes`: nullable, string

**Response:**

```json
{
    "success": true,
    "message": "Communication logged successfully",
    "data": {
        /* Communication object */
    }
}
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
    "success": false,
    "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Donor not found"
}
```

### 422 Validation Error

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "funding_amount": ["The funding amount must be at least 0.01."]
    }
}
```

### 500 Server Error

```json
{
    "success": false,
    "message": "An error occurred while processing your request"
}
```

---

## Rate Limiting

All API endpoints are subject to Laravel's default rate limiting:

- **60 requests per minute** for authenticated users
- Exceeding limit returns HTTP 429 (Too Many Requests)

---

## Pagination

List endpoints support pagination:

- Default: 25 items per page
- Maximum: 100 items per page
- Use `per_page` query parameter to adjust

Pagination metadata included in response:

```json
{
    "current_page": 1,
    "last_page": 3,
    "per_page": 25,
    "total": 72
}
```

---

## Filtering & Search

**Search Fields:**

- Donor name (partial match)
- Email (partial match)
- Contact person (partial match)

**Filterable Fields:**

- `status` - exact match (active/inactive)
- `funding_min` - greater than or equal

**Debounce:** Frontend implements 300ms search debounce

---

## Role-Based Permissions

### Programs Manager

- Full CRUD access to all donor endpoints
- Can assign/remove projects
- Can log communications
- Can generate reports

### Finance Officer

- Read-only access (list, view, statistics, funding summary)
- Can generate reports
- Cannot create, update, or delete

### Project Officer

- No access to donor module

---

## Related Models

### Donor

- **Relationships:** projects (many-to-many), inKindContributions (one-to-many), communications (polymorphic)
- **Soft Deletes:** Yes
- **Timestamps:** created_at, updated_at

### InKindContribution

- **Relationships:** donor (belongs to), project (belongs to), creator (belongs to User)
- **Categories:** equipment, supplies, services, training, other

### Communication

- **Relationships:** communicable (polymorphic), creator (belongs to User)
- **Types:** email, phone_call, meeting, letter, other
- **File Storage:** storage/app/communications/

---

## Best Practices

1. **Always validate** funding_amount > 0 before assignment
2. **Check active projects** before deleting or deactivating donor
3. **Use soft deletes** for data integrity
4. **Log all communications** for audit trail
5. **Restrict file uploads** to safe MIME types (pdf, doc, images)
6. **Implement proper error handling** on frontend for 422 validation errors

---

**Last Updated:** November 17, 2025
