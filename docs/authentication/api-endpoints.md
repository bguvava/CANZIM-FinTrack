# Authentication API Endpoints

## Base URL

```
http://localhost/api/v1/auth
```

All endpoints return JSON responses.

---

## 1. Login

Authenticate a user and receive an API token.

### Endpoint

```
POST /api/v1/auth/login
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
    "email": "programs-manager@canzim.org",
    "password": "password",
    "remember": false
}
```

**Parameters:**

| Field    | Type    | Required | Description                          |
| -------- | ------- | -------- | ------------------------------------ |
| email    | string  | Yes      | User's email address                 |
| password | string  | Yes      | User's password (min 8 characters)   |
| remember | boolean | No       | Remember me (30 days token vs 5 min) |

### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Programs Manager",
            "email": "programs-manager@canzim.org",
            "phone_number": "+263771234567",
            "role": {
                "id": 1,
                "name": "Programs Manager",
                "slug": "programs-manager"
            },
            "last_login_at": "2025-11-14T12:30:00.000000Z"
        },
        "token": "1|a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0"
    }
}
```

### Error Responses

**Invalid Credentials (401 Unauthorized)**

```json
{
    "status": "error",
    "message": "Invalid credentials"
}
```

**Account Locked (403 Forbidden)**

```json
{
    "status": "error",
    "message": "Your account has been locked due to multiple failed login attempts. Please try again later."
}
```

**Account Inactive (403 Forbidden)**

```json
{
    "status": "error",
    "message": "Your account is inactive. Please contact the administrator."
}
```

**Validation Error (422 Unprocessable Entity)**

```json
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### cURL Example

```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "programs-manager@canzim.org",
    "password": "password",
    "remember": true
  }'
```

### JavaScript Example

```javascript
const response = await fetch("http://localhost/api/v1/auth/login", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        email: "programs-manager@canzim.org",
        password: "password",
        remember: false,
    }),
});

const data = await response.json();
console.log(data.data.token); // Store this token
```

---

## 2. Logout

Revoke the current authentication token.

### Endpoint

```
POST /api/v1/auth/logout
```

### Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Request Body

None required.

### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

### Error Responses

**Unauthenticated (401 Unauthorized)**

```json
{
    "message": "Unauthenticated."
}
```

### cURL Example

```bash
curl -X POST http://localhost/api/v1/auth/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0"
```

### JavaScript Example

```javascript
const token = localStorage.getItem("authToken");

await fetch("http://localhost/api/v1/auth/logout", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
    },
});

// Clear local storage
localStorage.removeItem("authToken");
```

---

## 3. Forgot Password

Request a password reset link.

### Endpoint

```
POST /api/v1/auth/forgot-password
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
    "email": "programs-manager@canzim.org"
}
```

**Parameters:**

| Field | Type   | Required | Description          |
| ----- | ------ | -------- | -------------------- |
| email | string | Yes      | User's email address |

### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Password reset link sent to your email"
}
```

**Note:** For security, the same response is returned even if the email doesn't exist in the system.

### Error Responses

**Validation Error (422 Unprocessable Entity)**

```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### cURL Example

```bash
curl -X POST http://localhost/api/v1/auth/forgot-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "programs-manager@canzim.org"
  }'
```

### JavaScript Example

```javascript
await fetch("http://localhost/api/v1/auth/forgot-password", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        email: "programs-manager@canzim.org",
    }),
});
```

---

## 4. Reset Password

Reset user password using a valid token.

### Endpoint

```
POST /api/v1/auth/reset-password
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
    "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6",
    "email": "programs-manager@canzim.org",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Parameters:**

| Field                 | Type   | Required | Description                     |
| --------------------- | ------ | -------- | ------------------------------- |
| token                 | string | Yes      | Password reset token from email |
| email                 | string | Yes      | User's email address            |
| password              | string | Yes      | New password (min 8 characters) |
| password_confirmation | string | Yes      | Password confirmation           |

### Success Response (200 OK)

```json
{
    "status": "success",
    "message": "Password has been reset successfully"
}
```

### Error Responses

**Invalid Token (400 Bad Request)**

```json
{
    "status": "error",
    "message": "Invalid or expired password reset token"
}
```

**Validation Error (422 Unprocessable Entity)**

```json
{
    "message": "The password field confirmation does not match.",
    "errors": {
        "password": ["The password field confirmation does not match."]
    }
}
```

### cURL Example

```bash
curl -X POST http://localhost/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6",
    "email": "programs-manager@canzim.org",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### JavaScript Example

```javascript
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get("token");
const email = urlParams.get("email");

await fetch("http://localhost/api/v1/auth/reset-password", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        token: token,
        email: email,
        password: "newpassword123",
        password_confirmation: "newpassword123",
    }),
});
```

---

## 5. Get Profile

Retrieve authenticated user's profile information.

### Endpoint

```
GET /api/v1/auth/profile
```

### Headers

```
Accept: application/json
Authorization: Bearer {token}
```

### Request Body

None required.

### Success Response (200 OK)

```json
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "Programs Manager",
            "email": "programs-manager@canzim.org",
            "phone_number": "+263771234567",
            "role": {
                "id": 1,
                "name": "Programs Manager",
                "slug": "programs-manager"
            },
            "office_location": "Head Office",
            "status": "active",
            "last_login_at": "2025-11-14T12:30:00.000000Z",
            "created_at": "2025-11-14T10:00:00.000000Z"
        }
    }
}
```

### Error Responses

**Unauthenticated (401 Unauthorized)**

```json
{
    "message": "Unauthenticated."
}
```

### cURL Example

```bash
curl -X GET http://localhost/api/v1/auth/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0"
```

### JavaScript Example

```javascript
const token = localStorage.getItem("authToken");

const response = await fetch("http://localhost/api/v1/auth/profile", {
    headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
    },
});

const data = await response.json();
console.log(data.data.user);
```

---

## Authentication Flow Example

### Complete Login-to-Logout Workflow

```javascript
// 1. Login
const loginResponse = await fetch("/api/v1/auth/login", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        email: "programs-manager@canzim.org",
        password: "password",
        remember: false,
    }),
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// Store token
localStorage.setItem("authToken", token);

// 2. Make Authenticated Request
const profileResponse = await fetch("/api/v1/auth/profile", {
    headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
    },
});

const profile = await profileResponse.json();
console.log(profile.data.user);

// 3. Logout
await fetch("/api/v1/auth/logout", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
    },
});

// Clear token
localStorage.removeItem("authToken");
```

---

## Rate Limiting

All authentication endpoints are rate-limited:

- **Login**: 5 attempts per minute per IP
- **Password Reset Request**: 3 attempts per hour per email
- **Password Reset**: 5 attempts per hour per IP

When rate limit is exceeded:

```json
{
    "message": "Too Many Attempts.",
    "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException"
}
```

**HTTP Status**: 429 Too Many Requests

---

## Security Best Practices

1. **Always use HTTPS in production**
2. **Store tokens securely** (httpOnly cookies or secure localStorage)
3. **Implement CSRF protection** for web applications
4. **Validate token on every request**
5. **Use short token expiry** (5 minutes default)
6. **Implement token refresh** mechanism
7. **Log all authentication events**
8. **Monitor for brute force attacks**
9. **Use strong password policies**
10. **Enable account lockout** (implemented: 5 failed attempts = 15 min lock)
