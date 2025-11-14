# Module 3: Authentication System - Overview

## Introduction

The CANZIM FinTrack Authentication System provides secure, role-based access control with advanced security features including account lockout, session management, and password reset functionality.

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (Vue 3)                        │
│  ┌────────────────┐  ┌──────────────┐  ┌────────────────┐  │
│  │ LandingPage.vue│  │ LoginForm.vue│  │  Auth Store    │  │
│  │   Component    │  │  Component   │  │   (Pinia)      │  │
│  └────────┬───────┘  └──────┬───────┘  └────────┬───────┘  │
└───────────┼──────────────────┼──────────────────┼───────────┘
            │                  │                  │
            └──────────────────┼──────────────────┘
                               │
                          API Calls
                               │
┌───────────────────────────────┼───────────────────────────────┐
│                   Backend (Laravel 12)                        │
│                               │                               │
│  ┌────────────────────────────▼──────────────────────────┐   │
│  │           API Routes (routes/api.php)                 │   │
│  │  POST /api/v1/auth/login                              │   │
│  │  POST /api/v1/auth/logout                             │   │
│  │  POST /api/v1/auth/forgot-password                    │   │
│  │  POST /api/v1/auth/reset-password                     │   │
│  │  GET  /api/v1/auth/profile                            │   │
│  └─────────────────────────┬─────────────────────────────┘   │
│                            │                                  │
│  ┌─────────────────────────▼─────────────────────────────┐   │
│  │        AuthController                                 │   │
│  │  - login()         - profile()                        │   │
│  │  - logout()        - forgotPassword()                 │   │
│  │  - resetPassword()                                    │   │
│  └─────────────────────────┬─────────────────────────────┘   │
│                            │                                  │
│  ┌─────────────────────────▼─────────────────────────────┐   │
│  │          AuthService (Business Logic)                 │   │
│  │  - login()              - sendPasswordResetLink()     │   │
│  │  - logout()             - resetPassword()             │   │
│  │  - handleFailedLogin()  - logFailedLogin()            │   │
│  └─────────────────────────┬─────────────────────────────┘   │
│                            │                                  │
│  ┌─────────────────────────▼─────────────────────────────┐   │
│  │           User Model & Database                       │   │
│  │  - isLocked()           - hasRole()                   │   │
│  │  - isActive()           - verifyPassword()            │   │
│  │  - lockAccount()        - updateLastLogin()           │   │
│  │  - incrementFailedLoginAttempts()                     │   │
│  │  - resetFailedLoginAttempts()                         │   │
│  └───────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌───────────────────────────────────────────────────────┐   │
│  │        Middleware & Security                          │   │
│  │  - Laravel Sanctum (Token Authentication)             │   │
│  │  - CheckRole (Role-Based Access Control)              │   │
│  │  - Session Timeout (5 min / 30 days with remember)    │   │
│  └───────────────────────────────────────────────────────┘   │
└───────────────────────────────────────────────────────────────┘
```

## Authentication Flow

### Login Process

```
1. User enters email & password → LoginForm.vue
2. Frontend validates input
3. POST /api/v1/auth/login → AuthController
4. AuthController → AuthService.login()
5. AuthService checks:
   - User exists?
   - Account locked? (5 failed attempts = 15 min lock)
   - Account active?
   - Password valid?
6. If successful:
   - Reset failed login attempts
   - Update last_login_at & last_login_ip
   - Create Sanctum token (5 min or 30 days)
   - Log audit trail
   - Return user data + token
7. If failed:
   - Increment failed_login_attempts
   - Lock account if attempts ≥ 5
   - Log failed attempt
   - Return error message
8. Frontend stores token in Pinia store
9. Axios interceptor adds token to all requests
```

### Logout Process

```
1. User clicks Logout → Auth Store
2. POST /api/v1/auth/logout with Bearer token
3. AuthController → AuthService.logout()
4. Revoke current Sanctum token
5. Clear frontend state (Pinia store)
6. Redirect to landing page
```

### Password Reset Flow

```
1. User clicks "Forgot Password" → Enter email
2. POST /api/v1/auth/forgot-password
3. AuthService → sendPasswordResetLink()
4. Generate password reset token
5. Send email with reset link (password.reset route)
6. User clicks link → Vue handles reset form
7. POST /api/v1/auth/reset-password with token + new password
8. Validate token (60 min expiry)
9. Update password (hashed)
10. Revoke all existing tokens
11. Log password reset in audit trail
```

## Security Features

### 1. Account Lockout

- **Trigger**: 5 consecutive failed login attempts
- **Duration**: 15 minutes
- **Implementation**: `locked_until` timestamp in users table
- **Unlock**: Automatic after timeout or admin intervention

### 2. Session Management

- **Default**: 5 minutes token expiry
- **Remember Me**: 30 days token expiry
- **Technology**: Laravel Sanctum API tokens
- **Frontend**: Pinia store with automatic logout on 401

### 3. Password Security

- **Hashing**: bcrypt (Laravel default)
- **Minimum Length**: 8 characters
- **Validation**: Confirmed password on reset
- **Reset Token**: 60-minute expiry

### 4. Audit Trail

All authentication events are logged:

- Login attempts (success/failure)
- Logout events
- Password resets
- Account lockouts
- IP addresses tracked

## Database Schema

### Users Table Fields

```sql
- id (bigint, primary key)
- name (varchar 255)
- email (varchar 255, unique)
- email_verified_at (timestamp, nullable)
- password (varchar 255, hashed)
- role_id (bigint, foreign key to roles)
- office_location (varchar 255, nullable)
- status (enum: 'active', 'inactive')
- phone_number (varchar 255, nullable)
- failed_login_attempts (integer, default 0)
- last_failed_login_at (timestamp, nullable)
- locked_until (timestamp, nullable)
- last_login_at (timestamp, nullable)
- last_login_ip (varchar 45, nullable)
- remember_token (varchar 100, nullable)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable - soft delete)
```

## Role-Based Access Control

### Roles

1. **Programs Manager** (slug: programs-manager)
    - Full system access
    - Can access all modules
2. **Finance Officer** (slug: finance-officer)
    - Financial management access
    - Budget and expense tracking
3. **Project Officer** (slug: project-officer)
    - Project-specific access
    - Limited financial visibility

### Middleware Implementation

```php
// Protect route with role middleware
Route::middleware(['auth:sanctum', 'role:programs-manager'])
    ->get('/admin/dashboard', [AdminController::class, 'index']);

// Multiple roles allowed
Route::middleware(['auth:sanctum', 'role:finance-officer,programs-manager'])
    ->get('/budgets', [BudgetController::class, 'index']);
```

## Testing Coverage

### Test Statistics

- **Total Tests**: 49
- **Pass Rate**: 100%
- **Test Files**: 5
- **Assertions**: 116

### Test Categories

1. **Unit Tests** (13 tests)
    - User model methods
    - isLocked(), isActive()
    - Password verification
    - Role checking

2. **Feature Tests** (36 tests)
    - Login functionality (10 tests)
    - Password reset (9 tests)
    - Role middleware (7 tests)
    - Landing page (6 tests)
    - Environment setup (4 tests)

## Frontend Components

### LandingPage.vue

- Main entry point for unauthenticated users
- Displays LoginForm component
- Responsive design with Tailwind CSS
- Font Awesome icons integrated

### LoginForm.vue

- Email & password inputs
- "Remember Me" checkbox
- "Forgot Password" link
- Client-side validation
- Loading states
- SweetAlert2 for notifications

### Pinia Auth Store

```javascript
// State
- user: null | User
- token: null | string
- isAuthenticated: boolean
- sessionTimeout: number

// Actions
- login(email, password, remember)
- logout()
- checkAuth()
- handleSessionTimeout()

// Getters
- currentUser
- userRole
- isLoggedIn
```

## Configuration

### Environment Variables

```env
# App
APP_NAME="CANZIM FinTrack"
APP_URL=http://localhost

# Session
SESSION_LIFETIME=120
SESSION_DRIVER=database

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Mail (for password reset)
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@canzim.org"
```

### Sanctum Configuration

```php
// config/sanctum.php
'expiration' => null, // Controlled per-token
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

## Error Handling

### HTTP Status Codes

- **200**: Successful authentication
- **401**: Invalid credentials or unauthorized
- **403**: Account locked or inactive
- **422**: Validation errors
- **500**: Server errors

### Error Messages

All error messages are user-friendly and logged for debugging:

```json
{
    "status": "error",
    "message": "Your account has been locked due to multiple failed login attempts. Please try again later."
}
```

## Performance Considerations

1. **Database Indexes**
    - `email` (unique index)
    - `role_id` (foreign key index)
    - `status` (index for active/inactive queries)

2. **Query Optimization**
    - Eager load role relationship
    - Use `firstWhere()` for single record lookups
    - Cache role lookups in frontend

3. **Token Management**
    - Automatic token cleanup (Laravel Sanctum)
    - Single token per session (revoke on logout)

## Future Enhancements

1. **Two-Factor Authentication (2FA)**
    - SMS or email OTP
    - Google Authenticator support

2. **Password Policy**
    - Complexity requirements
    - Password history
    - Expiry after 90 days

3. **Social Login**
    - Google OAuth
    - Microsoft Azure AD

4. **Biometric Authentication**
    - Fingerprint (mobile)
    - Face recognition

## Conclusion

The CANZIM FinTrack authentication system provides enterprise-grade security with a seamless user experience. All 49 tests passing ensures reliability and maintainability for future development.
