# Comments API Endpoints Documentation

## Base URL

```
/api/v1
```

All endpoints require authentication via Laravel Sanctum tokens.

---

## Authentication

Include the Sanctum token in the request headers:

```http
Authorization: Bearer {token}
Accept: application/json
```

---

## Endpoints Overview

| Method    | Endpoint                             | Description                 |
| --------- | ------------------------------------ | --------------------------- |
| GET       | `/comments`                          | List comments for an entity |
| POST      | `/comments`                          | Create a new comment        |
| GET       | `/comments/{id}`                     | Get a single comment        |
| PUT/PATCH | `/comments/{id}`                     | Update a comment            |
| DELETE    | `/comments/{id}`                     | Delete a comment            |
| GET       | `/comment-attachments/{id}/download` | Download attachment         |

---

## 1. List Comments

Retrieve paginated comments for a specific entity.

### Request

```http
GET /api/v1/comments?commentable_type={type}&commentable_id={id}
```

### Query Parameters

| Parameter          | Type    | Required | Description                                 |
| ------------------ | ------- | -------- | ------------------------------------------- |
| `commentable_type` | string  | Yes      | Entity type: 'Project', 'Budget', 'Expense' |
| `commentable_id`   | integer | Yes      | Entity ID                                   |
| `page`             | integer | No       | Page number (default: 1)                    |
| `per_page`         | integer | No       | Results per page (default: 15)              |

### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/comments?commentable_type=Project&commentable_id=1&page=1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "data": [
        {
            "id": 1,
            "commentable_type": "Project",
            "commentable_id": 1,
            "user_id": 5,
            "parent_id": null,
            "content": "Great progress on this project! @john please review the budget.",
            "created_at": "2025-11-19T10:30:00.000000Z",
            "updated_at": "2025-11-19T10:30:00.000000Z",
            "user": {
                "id": 5,
                "name": "Jane Doe",
                "email": "jane@example.com"
            },
            "attachments": [
                {
                    "id": 1,
                    "comment_id": 1,
                    "file_name": "budget-review.pdf",
                    "file_size": 245760,
                    "mime_type": "application/pdf",
                    "created_at": "2025-11-19T10:30:00.000000Z"
                }
            ],
            "replies": [
                {
                    "id": 2,
                    "parent_id": 1,
                    "content": "Thanks! I'll review it today.",
                    "user": {
                        "id": 6,
                        "name": "John Smith"
                    },
                    "created_at": "2025-11-19T11:15:00.000000Z"
                }
            ]
        }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
}
```

### Error Responses

**401 Unauthorized** - Missing or invalid token

```json
{
    "message": "Unauthenticated."
}
```

**422 Validation Error** - Missing required parameters

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "commentable_type": ["The commentable type field is required."],
        "commentable_id": ["The commentable id field is required."]
    }
}
```

---

## 2. Create Comment

Create a new comment (root or reply) with optional file attachments.

### Request

```http
POST /api/v1/comments
Content-Type: multipart/form-data
```

### Request Body (FormData)

| Field              | Type    | Required | Description                                 |
| ------------------ | ------- | -------- | ------------------------------------------- |
| `commentable_type` | string  | Yes      | Entity type: 'Project', 'Budget', 'Expense' |
| `commentable_id`   | integer | Yes      | Entity ID                                   |
| `content`          | string  | Yes      | Comment text (max 5000 chars)               |
| `parent_id`        | integer | No       | Parent comment ID (for threading)           |
| `attachments[]`    | file[]  | No       | File uploads (max 3, 2MB each)              |

### Example Request

```bash
curl -X POST "http://localhost:8000/api/v1/comments" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "commentable_type=Project" \
  -F "commentable_id=1" \
  -F "content=This is a great update! @jane please check." \
  -F "attachments[0]=@/path/to/file1.pdf" \
  -F "attachments[1]=@/path/to/image.jpg"
```

### Success Response (201 Created)

```json
{
    "message": "Comment created successfully",
    "data": {
        "id": 10,
        "commentable_type": "Project",
        "commentable_id": 1,
        "user_id": 5,
        "parent_id": null,
        "content": "This is a great update! @jane please check.",
        "created_at": "2025-11-19T14:30:00.000000Z",
        "updated_at": "2025-11-19T14:30:00.000000Z",
        "user": {
            "id": 5,
            "name": "John Smith",
            "email": "john@example.com"
        },
        "attachments": [
            {
                "id": 5,
                "comment_id": 10,
                "file_name": "file1.pdf",
                "file_size": 102400,
                "mime_type": "application/pdf",
                "created_at": "2025-11-19T14:30:00.000000Z"
            },
            {
                "id": 6,
                "comment_id": 10,
                "file_name": "image.jpg",
                "file_size": 204800,
                "mime_type": "image/jpeg",
                "created_at": "2025-11-19T14:30:00.000000Z"
            }
        ],
        "replies": []
    }
}
```

### Validation Rules

- `commentable_type`: required, string, must be 'Project', 'Budget', or 'Expense'
- `commentable_id`: required, integer, must exist in respective table
- `content`: required, string, max 5000 characters
- `parent_id`: optional, integer, must be a valid comment ID
- `attachments.*`: optional, file, max 2MB, allowed types: pdf, doc, docx, jpg, jpeg, png
- `attachments`: max 3 files total

### Error Responses

**422 Validation Error**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "content": ["The content field is required."],
        "attachments.0": ["The file must not be greater than 2048 kilobytes."],
        "attachments.3": ["You may upload a maximum of 3 attachments."]
    }
}
```

---

## 3. Get Single Comment

Retrieve a specific comment by ID.

### Request

```http
GET /api/v1/comments/{id}
```

### Path Parameters

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `id`      | integer | Yes      | Comment ID  |

### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/comments/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (200 OK)

```json
{
    "data": {
        "id": 1,
        "commentable_type": "Project",
        "commentable_id": 1,
        "user_id": 5,
        "parent_id": null,
        "content": "Great progress on this project!",
        "created_at": "2025-11-19T10:30:00.000000Z",
        "updated_at": "2025-11-19T10:30:00.000000Z",
        "user": {
            "id": 5,
            "name": "Jane Doe",
            "email": "jane@example.com"
        },
        "attachments": [],
        "replies": []
    }
}
```

### Error Responses

**404 Not Found**

```json
{
    "message": "Comment not found."
}
```

---

## 4. Update Comment

Update an existing comment (content only). Users can only update their own comments.

### Request

```http
PUT /api/v1/comments/{id}
PATCH /api/v1/comments/{id}
Content-Type: application/json
```

### Path Parameters

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `id`      | integer | Yes      | Comment ID  |

### Request Body (JSON)

| Field     | Type   | Required | Description                           |
| --------- | ------ | -------- | ------------------------------------- |
| `content` | string | Yes      | Updated comment text (max 5000 chars) |

### Example Request

```bash
curl -X PUT "http://localhost:8000/api/v1/comments/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Updated comment content here."
  }'
```

### Success Response (200 OK)

```json
{
    "message": "Comment updated successfully",
    "data": {
        "id": 1,
        "content": "Updated comment content here.",
        "updated_at": "2025-11-19T15:00:00.000000Z",
        "user": {
            "id": 5,
            "name": "Jane Doe"
        }
    }
}
```

### Error Responses

**403 Forbidden** - Cannot update other user's comment

```json
{
    "message": "You do not have permission to perform this action."
}
```

**422 Validation Error**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "content": ["The content field is required."]
    }
}
```

---

## 5. Delete Comment

Soft delete a comment. Users can only delete their own comments. Deleted comments preserve thread structure (replies remain visible).

### Request

```http
DELETE /api/v1/comments/{id}
```

### Path Parameters

| Parameter | Type    | Required | Description |
| --------- | ------- | -------- | ----------- |
| `id`      | integer | Yes      | Comment ID  |

### Example Request

```bash
curl -X DELETE "http://localhost:8000/api/v1/comments/1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Success Response (204 No Content)

No response body. HTTP status 204 indicates successful deletion.

### Error Responses

**403 Forbidden** - Cannot delete other user's comment

```json
{
    "message": "You do not have permission to perform this action."
}
```

**404 Not Found**

```json
{
    "message": "Comment not found."
}
```

---

## 6. Download Attachment

Download a comment attachment file. Authenticated users only.

### Request

```http
GET /api/v1/comment-attachments/{id}/download
```

### Path Parameters

| Parameter | Type    | Required | Description   |
| --------- | ------- | -------- | ------------- |
| `id`      | integer | Yes      | Attachment ID |

### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/comment-attachments/1/download" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  --output downloaded-file.pdf
```

### Success Response (200 OK)

Binary file content with appropriate headers:

```
Content-Type: application/pdf (or image/jpeg, etc.)
Content-Disposition: attachment; filename="budget-review.pdf"
```

### Error Responses

**404 Not Found**

```json
{
    "message": "Attachment not found or file does not exist."
}
```

**401 Unauthorized**

```json
{
    "message": "Unauthenticated."
}
```

---

## Response Structure

### Success Response Format

```json
{
    "message": "Operation completed successfully",
    "data": {
        /* response data */
    }
}
```

### Error Response Format

```json
{
    "message": "Error message",
    "errors": {
        "field_name": ["Error description"]
    }
}
```

### HTTP Status Codes

| Code | Meaning                                 |
| ---- | --------------------------------------- |
| 200  | OK - Request successful                 |
| 201  | Created - Resource created              |
| 204  | No Content - Resource deleted           |
| 401  | Unauthorized - Missing/invalid token    |
| 403  | Forbidden - No permission               |
| 404  | Not Found - Resource not found          |
| 422  | Unprocessable Entity - Validation error |
| 500  | Internal Server Error - Server error    |

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **Authenticated:** 60 requests per minute
- **Downloads:** 30 requests per minute

Rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1700416800
```

---

## Best Practices

### Creating Comments

1. Always escape user input to prevent XSS
2. Validate file types and sizes before upload
3. Use FormData for multipart requests with attachments
4. Handle @mentions on frontend for better UX

### Fetching Comments

1. Always include `commentable_type` and `commentable_id`
2. Use pagination for large comment threads
3. Implement eager loading on backend to prevent N+1 queries

### Updating Comments

1. Only allow content updates (no entity changes)
2. Show "edited" indicator in UI
3. Validate ownership before update

### Deleting Comments

1. Use soft deletes to preserve thread structure
2. Confirm deletion with user before API call
3. Show [deleted] placeholder in UI for deleted comments with replies

---

## JavaScript/Vue Example

```javascript
import axios from "axios";

// List comments
async function fetchComments(entityType, entityId, page = 1) {
    const response = await axios.get("/api/v1/comments", {
        params: {
            commentable_type: entityType,
            commentable_id: entityId,
            page: page,
        },
    });
    return response.data;
}

// Create comment with attachments
async function createComment(data, files = []) {
    const formData = new FormData();
    formData.append("commentable_type", data.commentable_type);
    formData.append("commentable_id", data.commentable_id);
    formData.append("content", data.content);

    if (data.parent_id) {
        formData.append("parent_id", data.parent_id);
    }

    files.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    const response = await axios.post("/api/v1/comments", formData, {
        headers: { "Content-Type": "multipart/form-data" },
    });
    return response.data;
}

// Update comment
async function updateComment(id, content) {
    const response = await axios.put(`/api/v1/comments/${id}`, {
        content: content,
    });
    return response.data;
}

// Delete comment
async function deleteComment(id) {
    await axios.delete(`/api/v1/comments/${id}`);
}

// Download attachment
function downloadAttachment(id, filename) {
    const url = `/api/v1/comment-attachments/${id}/download`;
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
}
```

---

## Testing with Postman

### 1. Set Up Environment Variables

```json
{
    "base_url": "http://localhost:8000/api/v1",
    "token": "YOUR_SANCTUM_TOKEN"
}
```

### 2. Add Authorization Header

```
Authorization: Bearer {{token}}
```

### 3. Test Create Comment

**URL:** `{{base_url}}/comments`
**Method:** POST
**Body (form-data):**

- commentable_type: Project
- commentable_id: 1
- content: Test comment from Postman
- attachments[0]: [select file]

---

## Troubleshooting

### Issue: 401 Unauthorized

**Solution:** Verify Sanctum token is included in Authorization header

### Issue: 422 Validation Error on File Upload

**Solution:** Check file size (< 2MB), file type (PDF/DOC/images), max 3 files

### Issue: 403 Forbidden on Update/Delete

**Solution:** Users can only modify their own comments

### Issue: Comments not loading

**Solution:** Verify `commentable_type` and `commentable_id` are correct

---

**Last Updated:** November 19, 2025  
**API Version:** v1.0.0
