# User Management - API Documentation

## Overview

This document provides comprehensive API documentation for the User Management System (Module 4) in CANZIM FinTrack. All endpoints require authentication via Laravel Sanctum token.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

All endpoints require a valid Sanctum authentication token in the request header:

```
Authorization: Bearer {your-token}
```

---

## Table of Contents

1. [User Management Endpoints](#user-management-endpoints)
2. [User Profile Endpoints](#user-profile-endpoints)
3. [Activity Logs Endpoints](#activity-logs-endpoints)
4. [Utility Endpoints](#utility-endpoints)
5. [Error Responses](#error-responses)

---

## User Management Endpoints

### 1. List All Users

**Endpoint:** `GET /api/v1/users`

**Authorization:** Programs Manager only

**Query Parameters:**

| Parameter       | Type   | Required | Description                               |
| --------------- | ------ | -------- | ----------------------------------------- |
| page            | int    | No       | Page number for pagination (default: 1)   |
| per_page        | int    | No       | Items per page (default: 25)              |
| search          | string | No       | Search by name or email                   |
| role_id         | int    | No       | Filter by role ID                         |
| status          | string | No       | Filter by status (`active` or `inactive`) |
| office_location | string | No       | Filter by office location                 |

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "role_id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone_number": "+263771234567",
            "avatar_path": "avatars/user_1_20250115120000.jpg",
            "avatar_url": "http://example.com/storage/avatars/user_1_20250115120000.jpg",
            "initials": "JD",
            "office_location": "Harare",
            "status": "active",
            "email_verified_at": "2025-01-15T12:00:00.000000Z",
            "last_login_at": "2025-01-15T14:30:00.000000Z",
            "created_at": "2025-01-15T10:00:00.000000Z",
            "updated_at": "2025-01-15T14:30:00.000000Z",
            "role": {
                "id": 1,
                "name": "Programs Manager",
                "slug": "programs-manager",
                "description": "Full access to all system features"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "per_page": 25,
        "to": 25,
        "total": 75
    }
}
```

---

### 2. Create New User

**Endpoint:** `POST /api/v1/users`

**Authorization:** Programs Manager only

**Request Body:**

```json
{
    "role_id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone_number": "+263771234568",
    "office_location": "Bulawayo",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!"
}
```

**Validation Rules:**

| Field                 | Rules                                  |
| --------------------- | -------------------------------------- |
| role_id               | required, exists in roles table        |
| name                  | required, string, max:255              |
| email                 | required, email, unique in users table |
| phone_number          | nullable, string, max:20               |
| office_location       | nullable, string, max:255              |
| password              | required, string, min:8, confirmed     |
| password_confirmation | required, must match password          |

**Success Response (201 Created):**

```json
{
    "message": "User created successfully",
    "data": {
        "id": 26,
        "role_id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "phone_number": "+263771234568",
        "avatar_path": null,
        "avatar_url": null,
        "initials": "JS",
        "office_location": "Bulawayo",
        "status": "active",
        "email_verified_at": "2025-01-15T15:00:00.000000Z",
        "last_login_at": null,
        "created_at": "2025-01-15T15:00:00.000000Z",
        "updated_at": "2025-01-15T15:00:00.000000Z",
        "role": {
            "id": 2,
            "name": "Finance Officer",
            "slug": "finance-officer",
            "description": "Manages financial operations"
        }
    }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
    "message": "The given data was invalid",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

### 3. View User Details

**Endpoint:** `GET /api/v1/users/{id}`

**Authorization:**

- Programs Manager: Can view any user
- Other roles: Can only view their own profile

**Success Response (200 OK):**

```json
{
    "data": {
        "id": 1,
        "role_id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone_number": "+263771234567",
        "avatar_path": "avatars/user_1_20250115120000.jpg",
        "avatar_url": "http://example.com/storage/avatars/user_1_20250115120000.jpg",
        "initials": "JD",
        "office_location": "Harare",
        "status": "active",
        "email_verified_at": "2025-01-15T12:00:00.000000Z",
        "last_login_at": "2025-01-15T14:30:00.000000Z",
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T14:30:00.000000Z",
        "role": {
            "id": 1,
            "name": "Programs Manager",
            "slug": "programs-manager",
            "description": "Full access to all system features"
        }
    }
}
```

---

### 4. Update User

**Endpoint:** `PUT /api/v1/users/{id}`

**Authorization:**

- Programs Manager: Can update any user
- Other roles: Can only update their own profile

**Request Body:**

```json
{
    "role_id": 2,
    "name": "Jane Smith Updated",
    "email": "jane.updated@example.com",
    "phone_number": "+263771234569",
    "office_location": "Mutare"
}
```

**Validation Rules:**

| Field           | Rules                                         |
| --------------- | --------------------------------------------- |
| role_id         | required, exists in roles table               |
| name            | required, string, max:255                     |
| email           | required, email, unique (except current user) |
| phone_number    | nullable, string, max:20                      |
| office_location | nullable, string, max:255                     |

**Success Response (200 OK):**

```json
{
    "message": "User updated successfully",
    "data": {
        "id": 26,
        "role_id": 2,
        "name": "Jane Smith Updated",
        "email": "jane.updated@example.com",
        "phone_number": "+263771234569",
        "office_location": "Mutare",
        "status": "active",
        "role": {
            "id": 2,
            "name": "Finance Officer",
            "slug": "finance-officer"
        }
    }
}
```

---

### 5. Deactivate User

**Endpoint:** `POST /api/v1/users/{id}/deactivate`

**Authorization:** Programs Manager only (cannot deactivate self)

**Success Response (200 OK):**

```json
{
    "message": "User deactivated successfully",
    "data": {
        "id": 26,
        "name": "Jane Smith",
        "status": "inactive"
    }
}
```

**Error Response (403 Forbidden):**

```json
{
    "message": "You cannot deactivate yourself"
}
```

---

### 6. Activate User

**Endpoint:** `POST /api/v1/users/{id}/activate`

**Authorization:** Programs Manager only

**Success Response (200 OK):**

```json
{
    "message": "User activated successfully",
    "data": {
        "id": 26,
        "name": "Jane Smith",
        "status": "active"
    }
}
```

---

### 7. Delete User

**Endpoint:** `DELETE /api/v1/users/{id}`

**Authorization:** Programs Manager only (cannot delete self)

**Success Response (200 OK):**

```json
{
    "message": "User deleted successfully"
}
```

**Error Response (403 Forbidden):**

```json
{
    "message": "You cannot delete yourself"
}
```

---

## User Profile Endpoints

### 1. View Own Profile

**Endpoint:** `GET /api/v1/profile`

**Authorization:** All authenticated users

**Success Response (200 OK):**

```json
{
    "data": {
        "id": 1,
        "role_id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone_number": "+263771234567",
        "avatar_url": "http://example.com/storage/avatars/user_1_20250115120000.jpg",
        "office_location": "Harare",
        "status": "active",
        "role": {
            "id": 1,
            "name": "Programs Manager",
            "slug": "programs-manager"
        }
    }
}
```

---

### 2. Update Own Profile

**Endpoint:** `PUT /api/v1/profile`

**Authorization:** All authenticated users

**Request Body:**

```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone_number": "+263771234570",
    "office_location": "Gweru"
}
```

**Success Response (200 OK):**

```json
{
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "name": "John Doe Updated",
        "email": "john.updated@example.com",
        "phone_number": "+263771234570",
        "office_location": "Gweru"
    }
}
```

---

### 3. Change Password

**Endpoint:** `POST /api/v1/profile/change-password`

**Authorization:** All authenticated users

**Request Body:**

```json
{
    "current_password": "OldPassword123!",
    "password": "NewPassword456!",
    "password_confirmation": "NewPassword456!"
}
```

**Validation Rules:**

| Field                 | Rules                                  |
| --------------------- | -------------------------------------- |
| current_password      | required, must match existing password |
| password              | required, string, min:8, confirmed     |
| password_confirmation | required, must match password          |

**Success Response (200 OK):**

```json
{
    "message": "Password changed successfully. All other sessions have been logged out."
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
    "message": "The given data was invalid",
    "errors": {
        "current_password": ["The current password is incorrect."]
    }
}
```

---

### 4. Upload Avatar

**Endpoint:** `POST /api/v1/profile/avatar`

**Authorization:** All authenticated users

**Request Body:** (multipart/form-data)

```
avatar: [image file]
```

**Validation Rules:**

| Field  | Rules                                         |
| ------ | --------------------------------------------- |
| avatar | required, image, mimes:jpg,jpeg,png, max:2048 |

**Success Response (200 OK):**

```json
{
    "message": "Avatar uploaded successfully",
    "data": {
        "avatar_path": "avatars/user_1_20250115160000.jpg",
        "avatar_url": "http://example.com/storage/avatars/user_1_20250115160000.jpg"
    }
}
```

---

## Activity Logs Endpoints

### 1. List All Activity Logs

**Endpoint:** `GET /api/v1/users/activity-logs`

**Authorization:** Programs Manager only

**Query Parameters:**

| Parameter     | Type   | Required | Description                             |
| ------------- | ------ | -------- | --------------------------------------- |
| page          | int    | No       | Page number for pagination (default: 1) |
| per_page      | int    | No       | Items per page (default: 25)            |
| user_id       | int    | No       | Filter by user ID                       |
| activity_type | string | No       | Filter by activity type                 |
| date_from     | date   | No       | Filter from date (YYYY-MM-DD)           |
| date_to       | date   | No       | Filter to date (YYYY-MM-DD)             |

**Activity Types:**

- `user_created`
- `user_updated`
- `user_deactivated`
- `user_activated`
- `user_deleted`
- `password_changed`
- `profile_updated`
- `avatar_uploaded`
- `logs_bulk_deleted`

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user_name": "John Doe",
            "user_email": "john@example.com",
            "target_user_id": 26,
            "activity_type": "user_created",
            "description": "Created user Jane Smith",
            "changes": "{\"name\":\"Jane Smith\",\"email\":\"jane@example.com\",\"role\":\"Finance Officer\"}",
            "ip_address": "192.168.1.1",
            "user_agent": "Mozilla/5.0...",
            "created_at": "2025-01-15T15:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 25,
        "to": 25,
        "total": 250
    }
}
```

---

### 2. View User Activity

**Endpoint:** `GET /api/v1/users/{id}/activity`

**Authorization:**

- Programs Manager: Can view any user's activity
- Other roles: Can only view their own activity

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 5,
            "activity_type": "password_changed",
            "description": "Changed password",
            "changes": null,
            "created_at": "2025-01-15T16:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 2,
        "total": 50
    }
}
```

---

### 3. Bulk Delete Activity Logs

**Endpoint:** `POST /api/v1/users/activity-logs/bulk-delete`

**Authorization:** Programs Manager only

**Request Body:**

```json
{
    "date_from": "2025-01-01",
    "date_to": "2025-01-10"
}
```

**Validation Rules:**

| Field     | Rules                                    |
| --------- | ---------------------------------------- |
| date_from | required, date, before_or_equal:date_to  |
| date_to   | required, date, after_or_equal:date_from |

**Success Response (200 OK):**

```json
{
    "message": "Activity logs deleted successfully",
    "deleted_count": 120
}
```

---

## Utility Endpoints

### 1. Get Roles List

**Endpoint:** `GET /api/v1/users/roles/list`

**Authorization:** All authenticated users

**Success Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Programs Manager",
            "slug": "programs-manager"
        },
        {
            "id": 2,
            "name": "Finance Officer",
            "slug": "finance-officer"
        },
        {
            "id": 3,
            "name": "Project Officer",
            "slug": "project-officer"
        }
    ]
}
```

---

### 2. Get Office Locations List

**Endpoint:** `GET /api/v1/users/locations/list`

**Authorization:** All authenticated users

**Success Response (200 OK):**

```json
{
    "data": ["Harare", "Bulawayo", "Mutare", "Gweru", "Kwekwe"]
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

### 404 Not Found

```json
{
    "message": "User not found."
}
```

### 422 Unprocessable Entity

```json
{
    "message": "The given data was invalid",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### 500 Internal Server Error

```json
{
    "message": "An error occurred while processing your request."
}
```

---

## Rate Limiting

All API endpoints are rate-limited to:

- **60 requests per minute** for authenticated users
- **10 requests per minute** for unauthenticated users

When rate limit is exceeded, you'll receive a `429 Too Many Requests` response:

```json
{
    "message": "Too many requests. Please try again later."
}
```

---

## Postman Collection

A Postman collection with all endpoints and example requests is available at:
`docs/users/postman_collection.json`

---

## Support

For API support or to report issues, please contact:

- Email: support@canzim.org
- GitHub Issues: [github.com/canzim/fintrack/issues](https://github.com/canzim/fintrack/issues)
