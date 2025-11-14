# User Management API Endpoints

## Overview

RESTful API endpoints for managing users, profiles, and activity logs in CANZIM FinTrack.

**Base URL:** `/api/v1`  
**Authentication:** Required (Laravel Sanctum token)

---

## User Management Endpoints

### 1. List Users

**GET** `/users`

**Authorization:** Programs Manager only

**Query Parameters:**

- `search` (string, optional) - Search by name or email
- `role_id` (integer, optional) - Filter by role
- `status` (string, optional) - Filter by status (active/inactive)
- `office_location` (string, optional) - Filter by office location
- `per_page` (integer, optional) - Results per page (default: 25)

**Response:**

```json
{
  "status": "success",
  "data": [...],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

---

### 2. Create User

**POST** `/users`

**Authorization:** Programs Manager only

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+263771234567",
    "office_location": "Harare Office",
    "role_id": 2,
    "password": "SecurePass123"
}
```

**Response:** `201 Created`

---

### 3. View User

**GET** `/users/{id}`

**Authorization:** Programs Manager or self

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": {...},
    "status": "active"
  }
}
```

---

### 4. Update User

**PUT** `/users/{id}`

**Authorization:** Programs Manager only

**Request Body:**

```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone_number": "+263771234567",
    "office_location": "Bulawayo Office",
    "role_id": 3
}
```

---

### 5. Delete User

**DELETE** `/users/{id}`

**Authorization:** Programs Manager only (cannot delete self)

---

### 6. Deactivate User

**POST** `/users/{id}/deactivate`

**Authorization:** Programs Manager only (cannot deactivate self)

---

### 7. Activate User

**POST** `/users/{id}/activate`

**Authorization:** Programs Manager only

---

### 8. Get Roles List

**GET** `/users/roles/list`

**Authorization:** Authenticated users

**Response:**

```json
{
    "status": "success",
    "data": [
        { "id": 1, "name": "Programs Manager", "slug": "programs-manager" },
        { "id": 2, "name": "Finance Officer", "slug": "finance-officer" },
        { "id": 3, "name": "Project Officer", "slug": "project-officer" }
    ]
}
```

---

### 9. Get Office Locations

**GET** `/users/locations/list`

**Authorization:** Authenticated users

---

## Profile Endpoints

### 1. Get Current Profile

**GET** `/profile`

**Authorization:** Authenticated users

---

### 2. Update Profile

**PUT** `/profile`

**Authorization:** Authenticated users

**Request Body:**

```json
{
    "name": "Updated Name",
    "email": "updated@example.com",
    "phone_number": "+263771234567"
}
```

**Note:** Users cannot change their own role

---

### 3. Change Password

**POST** `/profile/change-password`

**Request Body:**

```json
{
    "current_password": "OldPassword123",
    "new_password": "NewPassword123",
    "new_password_confirmation": "NewPassword123"
}
```

---

### 4. Upload Avatar

**POST** `/profile/avatar`

**Request Body:** `multipart/form-data`

- `avatar` (file) - JPEG/PNG, max 2MB

---

## Activity Logs Endpoints

### 1. List Activity Logs

**GET** `/activity-logs`

**Authorization:** Programs Manager only

**Query Parameters:**

- `user_id` (integer, optional)
- `activity_type` (string, optional)
- `date_from` (date, optional)
- `date_to` (date, optional)
- `per_page` (integer, optional)

---

### 2. Get User Activity

**GET** `/users/{id}/activity`

**Authorization:** Programs Manager or self

---

### 3. Bulk Delete Logs

**POST** `/activity-logs/bulk-delete`

**Authorization:** Programs Manager only

**Request Body:**

```json
{
    "date_from": "2025-01-01",
    "date_to": "2025-01-31"
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
    "message": "This action is unauthorized."
}
```

### 422 Validation Error

```json
{
    "status": "error",
    "message": "Validation failed.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

### 404 Not Found

```json
{
    "message": "Resource not found."
}
```

---

## Activity Types Tracked

- `user_created` - New user account created
- `user_updated` - User account updated
- `user_deactivated` - User account deactivated
- `user_activated` - User account activated
- `user_deleted` - User account deleted
- `profile_updated` - User updated own profile
- `password_changed` - User changed password
- `avatar_uploaded` - User uploaded profile picture
- `logs_bulk_deleted` - Activity logs bulk deleted
- `login` - User logged in (tracked in AuthController)
- `logout` - User logged out (tracked in AuthController)
