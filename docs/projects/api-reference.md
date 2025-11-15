# API Reference - Project & Budget Management

This document provides comprehensive documentation for all API endpoints in Module 6.

---

## Table of Contents

1. [Authentication](#authentication)
2. [Project Endpoints](#project-endpoints)
3. [Budget Endpoints](#budget-endpoints)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [Code Examples](#code-examples)

---

## Authentication

All API endpoints require authentication using Laravel Sanctum.

**Headers Required:**

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## Project Endpoints

### 1. List All Projects

**Endpoint:** `GET /api/v1/projects`

**Description:** Retrieve a paginated list of projects with optional filters.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number (default: 1) |
| `per_page` | integer | No | Items per page (default: 25) |
| `search` | string | No | Search by name, code, or description |
| `status` | string | No | Filter by status (planning, active, on_hold, completed, cancelled) |
| `donor_id` | integer | No | Filter by assigned donor |

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Projects retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Clean Water Initiative",
                "code": "PROJ-2025-0001",
                "description": "Providing clean water access to rural communities",
                "start_date": "2025-01-15",
                "end_date": "2025-12-31",
                "status": "active",
                "location": "Lusaka, Zambia",
                "created_by": 1,
                "created_at": "2025-01-10T10:00:00.000000Z",
                "updated_at": "2025-01-10T10:00:00.000000Z",
                "deleted_at": null,
                "budget_summary": {
                    "total_allocated": 150000.00,
                    "total_spent": 75000.00,
                    "utilization_percentage": 50,
                    "alert_level": "warning"
                },
                "donors": [...],
                "creator": {...}
            }
        ],
        "first_page_url": "http://localhost/api/v1/projects?page=1",
        "from": 1,
        "last_page": 3,
        "last_page_url": "http://localhost/api/v1/projects?page=3",
        "links": [...],
        "next_page_url": "http://localhost/api/v1/projects?page=2",
        "path": "http://localhost/api/v1/projects",
        "per_page": 25,
        "prev_page_url": null,
        "to": 25,
        "total": 67
    }
}
```

---

### 2. Create Project

**Endpoint:** `POST /api/v1/projects`

**Description:** Create a new project with donors and team members.

**Request Body:**

```json
{
    "name": "Education Program 2025",
    "description": "Supporting schools in rural areas",
    "start_date": "2025-03-01",
    "end_date": "2025-12-31",
    "status": "planning",
    "location": "Ndola, Zambia",
    "donors": [
        {
            "donor_id": 1,
            "funding_amount": 100000.0,
            "is_restricted": false
        },
        {
            "donor_id": 2,
            "funding_amount": 50000.0,
            "is_restricted": true
        }
    ],
    "team_member_ids": [3, 5, 7]
}
```

**Validation Rules:**

- `name`: required, string, max:255
- `description`: nullable, string
- `start_date`: required, date
- `end_date`: required, date, after:start_date
- `status`: required, in:planning,active,on_hold,completed
- `location`: nullable, string, max:255
- `donors`: nullable, array
- `donors.*.donor_id`: required_with:donors, exists:donors,id
- `donors.*.funding_amount`: required_with:donors, numeric, min:0
- `donors.*.is_restricted`: boolean
- `team_member_ids`: nullable, array
- `team_member_ids.*`: exists:users,id

**Response:** `201 Created`

```json
{
    "success": true,
    "message": "Project created successfully",
    "data": {
        "id": 5,
        "name": "Education Program 2025",
        "code": "PROJ-2025-0005",
        "description": "Supporting schools in rural areas",
        "start_date": "2025-03-01",
        "end_date": "2025-12-31",
        "status": "planning",
        "location": "Ndola, Zambia",
        "created_by": 1,
        "created_at": "2025-11-15T14:30:00.000000Z",
        "updated_at": "2025-11-15T14:30:00.000000Z",
        "donors": [...],
        "team_members": [...]
    }
}
```

---

### 3. Get Single Project

**Endpoint:** `GET /api/v1/projects/{id}`

**Description:** Retrieve detailed information for a specific project.

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Project retrieved successfully",
    "data": {
        "id": 1,
        "name": "Clean Water Initiative",
        "code": "PROJ-2025-0001",
        "description": "Providing clean water access to rural communities",
        "start_date": "2025-01-15",
        "end_date": "2025-12-31",
        "status": "active",
        "location": "Lusaka, Zambia",
        "created_by": 1,
        "created_at": "2025-01-10T10:00:00.000000Z",
        "updated_at": "2025-01-10T10:00:00.000000Z",
        "budget_summary": {
            "total_allocated": 150000.00,
            "total_spent": 75000.00,
            "utilization_percentage": 50,
            "alert_level": "warning"
        },
        "donors": [
            {
                "id": 1,
                "name": "World Bank",
                "contact_person": "John Smith",
                "pivot": {
                    "funding_amount": 100000.00,
                    "is_restricted": false
                }
            }
        ],
        "budgets": [...],
        "team_members": [...],
        "creator": {...}
    }
}
```

---

### 4. Update Project

**Endpoint:** `PUT /api/v1/projects/{id}`

**Description:** Update an existing project.

**Request Body:**

```json
{
    "name": "Updated Project Name",
    "description": "Updated description",
    "start_date": "2025-01-15",
    "end_date": "2025-12-31",
    "status": "active",
    "location": "Lusaka, Zambia"
}
```

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Project updated successfully",
    "data": {...}
}
```

---

### 5. Delete Project

**Endpoint:** `DELETE /api/v1/projects/{id}`

**Description:** Soft delete a project.

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Project deleted successfully"
}
```

---

### 6. Archive Project

**Endpoint:** `POST /api/v1/projects/{id}/archive`

**Description:** Archive a project (sets status to 'cancelled').

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Project archived successfully",
    "data": {
        "id": 1,
        "status": "cancelled",
        ...
    }
}
```

---

### 7. Generate Project Report

**Endpoint:** `GET /api/v1/projects/{id}/report`

**Description:** Generate a PDF financial report for a project.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `format` | string | No | Report format (default: 'pdf') |

**Response:** `200 OK` (File Download)

- Content-Type: `application/pdf`
- Content-Disposition: `attachment; filename="project-{id}-report.pdf"`

---

## Budget Endpoints

### 1. List All Budgets

**Endpoint:** `GET /api/v1/budgets`

**Description:** Retrieve a paginated list of budgets with optional filters.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number (default: 1) |
| `per_page` | integer | No | Items per page (default: 25) |
| `project_id` | integer | No | Filter by project |
| `status` | string | No | Filter by status (pending, approved, rejected) |

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Budgets retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "project_id": 1,
                "status": "approved",
                "total_allocated": 150000.00,
                "total_spent": 75000.00,
                "utilization_percentage": 50,
                "alert_level": "warning",
                "approved_by": 2,
                "approved_at": "2025-01-12T10:00:00.000000Z",
                "notes": "Approved with minor adjustments",
                "created_at": "2025-01-11T10:00:00.000000Z",
                "updated_at": "2025-01-12T10:00:00.000000Z",
                "project": {...},
                "items": [...]
            }
        ],
        ...
    }
}
```

---

### 2. Create Budget

**Endpoint:** `POST /api/v1/budgets`

**Description:** Create a new budget for a project.

**Request Body:**

```json
{
    "project_id": 1,
    "notes": "Initial budget for Q1 2025",
    "items": [
        {
            "category": "travel",
            "description": "Field visit transportation",
            "quantity": 10,
            "unit_cost": 500.0
        },
        {
            "category": "staff_salaries",
            "description": "Project coordinator salary",
            "quantity": 12,
            "unit_cost": 3000.0
        },
        {
            "category": "procurement",
            "description": "Water pumps and supplies",
            "quantity": 5,
            "unit_cost": 8000.0
        }
    ]
}
```

**Validation Rules:**

- `project_id`: required, exists:projects,id
- `notes`: nullable, string
- `items`: required, array, min:1
- `items.*.category`: required, in:travel,staff_salaries,procurement,consultants,other
- `items.*.description`: required, string, max:255
- `items.*.quantity`: required, numeric, min:1
- `items.*.unit_cost`: required, numeric, min:0

**Response:** `201 Created`

```json
{
    "success": true,
    "message": "Budget created successfully",
    "data": {
        "id": 3,
        "project_id": 1,
        "status": "pending",
        "total_allocated": 81000.00,
        "total_spent": 0.00,
        "utilization_percentage": 0,
        "alert_level": null,
        "items": [...]
    }
}
```

---

### 3. Get Single Budget

**Endpoint:** `GET /api/v1/budgets/{id}`

**Description:** Retrieve detailed information for a specific budget.

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Budget retrieved successfully",
    "data": {
        "id": 1,
        "project_id": 1,
        "status": "approved",
        "total_allocated": 150000.00,
        "total_spent": 75000.00,
        "utilization_percentage": 50,
        "alert_level": "warning",
        "items": [
            {
                "id": 1,
                "budget_id": 1,
                "category": "travel",
                "description": "Field visit transportation",
                "quantity": 10,
                "unit_cost": 500.00,
                "total_amount": 5000.00,
                "created_at": "2025-01-11T10:00:00.000000Z",
                "updated_at": "2025-01-11T10:00:00.000000Z"
            }
        ],
        "project": {...}
    }
}
```

---

### 4. Approve Budget

**Endpoint:** `POST /api/v1/budgets/{id}/approve`

**Description:** Approve a pending budget.

**Request Body:**

```json
{
    "notes": "Budget approved. Proceed with execution."
}
```

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Budget approved successfully",
    "data": {
        "id": 1,
        "status": "approved",
        "approved_by": 2,
        "approved_at": "2025-11-15T15:00:00.000000Z",
        "notes": "Budget approved. Proceed with execution.",
        ...
    }
}
```

---

### 5. Get Budget Categories

**Endpoint:** `GET /api/v1/budgets/categories`

**Description:** Retrieve all available budget categories.

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Budget categories retrieved successfully",
    "data": ["travel", "staff_salaries", "procurement", "consultants", "other"]
}
```

---

### 6. Request Budget Reallocation

**Endpoint:** `POST /api/v1/budgets/{id}/reallocations`

**Description:** Request to reallocate funds between budget items.

**Request Body:**

```json
{
    "from_budget_item_id": 1,
    "to_budget_item_id": 2,
    "amount": 5000.0,
    "justification": "Need additional funds for procurement due to price increases"
}
```

**Validation Rules:**

- `from_budget_item_id`: required, exists:budget_items,id
- `to_budget_item_id`: required, exists:budget_items,id, different:from_budget_item_id
- `amount`: required, numeric, min:0.01
- `justification`: required, string, max:500

**Response:** `201 Created`

```json
{
    "success": true,
    "message": "Budget reallocation requested successfully",
    "data": {
        "id": 1,
        "budget_id": 1,
        "from_budget_item_id": 1,
        "to_budget_item_id": 2,
        "amount": 5000.0,
        "justification": "Need additional funds for procurement due to price increases",
        "status": "pending",
        "requested_by": 1,
        "created_at": "2025-11-15T15:30:00.000000Z"
    }
}
```

---

### 7. Approve Budget Reallocation

**Endpoint:** `POST /api/v1/budgets/{budgetId}/reallocations/{reallocationId}/approve`

**Description:** Approve a budget reallocation request.

**Request Body:**

```json
{
    "notes": "Reallocation approved based on justification provided"
}
```

**Response:** `200 OK`

```json
{
    "success": true,
    "message": "Budget reallocation approved successfully",
    "data": {
        "id": 1,
        "status": "approved",
        "approved_by": 2,
        "approved_at": "2025-11-15T16:00:00.000000Z",
        "notes": "Reallocation approved based on justification provided",
        ...
    }
}
```

---

## Response Format

All API responses follow a consistent JSON structure:

### Success Response

```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {...}
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message describing what went wrong",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

---

## Error Handling

### HTTP Status Codes

| Code  | Meaning               | When Used                          |
| ----- | --------------------- | ---------------------------------- |
| `200` | OK                    | Successful GET, PUT, DELETE        |
| `201` | Created               | Successful POST (resource created) |
| `400` | Bad Request           | Malformed request                  |
| `401` | Unauthorized          | Missing or invalid authentication  |
| `403` | Forbidden             | User lacks permission              |
| `404` | Not Found             | Resource doesn't exist             |
| `422` | Unprocessable Entity  | Validation failed                  |
| `500` | Internal Server Error | Server error                       |

### Common Error Responses

**Validation Error (422):**

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "end_date": ["The end date must be a date after start date."]
    }
}
```

**Authorization Error (403):**

```json
{
    "success": false,
    "message": "This action is unauthorized."
}
```

**Not Found Error (404):**

```json
{
    "success": false,
    "message": "Project not found"
}
```

---

## Code Examples

### JavaScript (Axios)

```javascript
// List projects with filters
const fetchProjects = async () => {
    try {
        const response = await axios.get("/api/v1/projects", {
            params: {
                search: "water",
                status: "active",
                page: 1,
                per_page: 25,
            },
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
        });

        console.log(response.data.data);
    } catch (error) {
        console.error("Error:", error.response.data);
    }
};

// Create project
const createProject = async (projectData) => {
    try {
        const response = await axios.post("/api/v1/projects", projectData, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
        });

        return response.data.data;
    } catch (error) {
        throw error.response.data.errors;
    }
};

// Approve budget
const approveBudget = async (budgetId, notes) => {
    try {
        const response = await axios.post(
            `/api/v1/budgets/${budgetId}/approve`,
            { notes },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
            },
        );

        return response.data.data;
    } catch (error) {
        console.error("Approval failed:", error.response.data);
    }
};
```

### PHP (Laravel HTTP Client)

```php
use Illuminate\Support\Facades\Http;

// List projects
$response = Http::withToken($token)
    ->get('http://localhost/api/v1/projects', [
        'search' => 'water',
        'status' => 'active',
        'page' => 1
    ]);

$projects = $response->json('data');

// Create project
$response = Http::withToken($token)
    ->post('http://localhost/api/v1/projects', [
        'name' => 'New Project',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'status' => 'planning',
        'donors' => [
            [
                'donor_id' => 1,
                'funding_amount' => 100000,
                'is_restricted' => false
            ]
        ]
    ]);

if ($response->successful()) {
    $project = $response->json('data');
}
```

### cURL

```bash
# List projects
curl -X GET "http://localhost/api/v1/projects?search=water&status=active" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Create project
curl -X POST "http://localhost/api/v1/projects" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Project",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "status": "planning"
  }'

# Approve budget
curl -X POST "http://localhost/api/v1/budgets/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Approved"}'
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 10 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1699999999
```

---

## Versioning

The API is versioned using URL path versioning:

- Current version: `v1`
- Base URL: `/api/v1/`

Future versions will be released as `/api/v2/`, etc., ensuring backward compatibility.
