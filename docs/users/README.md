# Module 4: User Management System

## Overview

The User Management System is a comprehensive module that enables Programs Managers to manage all system users, monitor activity, and control access permissions. It provides full CRUD operations for users, role-based access control, activity logging, and profile management.

## Features

### ✅ User Management (Programs Manager Only)

- View all system users with pagination
- Search users by name or email
- Filter by role, status, and office location
- Create new user accounts with role assignment
- Edit user information and change roles
- Deactivate/Activate user accounts
- Delete user accounts (soft delete)
- Cannot delete or deactivate self (protection)

### ✅ User Profile Management (All Users)

- View own profile
- Edit profile information (name, email, phone, location)
- Change password with current password verification
- Upload and manage avatar (auto-resize to 200x200)
- View own activity history

### ✅ Activity Logs (Programs Manager Only)

- View all system activity logs
- Filter by user, activity type, and date range
- Monitor user actions and changes
- Bulk delete old logs by date range
- All actions are automatically logged

### ✅ Role-Based Access Control

- **Programs Manager**: Full access to all features
- **Finance Officer**: Limited to own profile management
- **Project Officer**: Limited to own profile management
- Authorization enforced via Laravel Policies

## Documentation

| Document                                  | Description                                   |
| ----------------------------------------- | --------------------------------------------- |
| [API Documentation](api-documentation.md) | Complete API endpoints with examples          |
| [User Guide](user-guide.md)               | Step-by-step user guide for all features      |
| [Testing Guide](testing-guide.md)         | Test coverage, running tests, and maintenance |

## Technical Stack

### Backend

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Authorization**: Laravel Policies
- **Validation**: Form Requests
- **API Resources**: Eloquent Resources
- **Storage**: Laravel Storage (public disk)
- **Image Processing**: Intervention Image

### Frontend

- **Framework**: Vue 3 Composition API
- **Styling**: Tailwind CSS 4
- **Icons**: Font Awesome 6.5.1
- **Notifications**: SweetAlert2
- **State Management**: Pinia
- **HTTP Client**: Axios

### Testing

- **Framework**: PHPUnit 11
- **Tests**: 103 tests, 367 assertions
- **Coverage**: 100% pass rate
- **Database**: RefreshDatabase trait

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php
│   │   ├── UserProfileController.php
│   │   └── ActivityLogController.php
│   ├── Requests/
│   │   ├── StoreUserRequest.php
│   │   ├── UpdateUserRequest.php
│   │   ├── UpdateProfileRequest.php
│   │   ├── ChangePasswordRequest.php
│   │   ├── BulkDeleteLogsRequest.php
│   │   └── UploadAvatarRequest.php
│   └── Resources/
│       ├── UserResource.php
│       └── ActivityLogResource.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   └── ActivityLog.php
├── Policies/
│   └── UserPolicy.php
└── Services/
    └── UserService.php

resources/
├── js/
│   ├── pages/
│   │   ├── Users.vue
│   │   └── ActivityLogs.vue
│   ├── components/
│   │   ├── Sidebar.vue
│   │   └── modals/
│   │       ├── AddUserModal.vue
│   │       ├── EditUserModal.vue
│   │       └── ViewUserModal.vue
│   └── layouts/
│       └── DashboardLayout.vue
└── views/
    ├── users.blade.php
    └── activity-logs.blade.php

routes/
└── api.php (User Management routes)

tests/
├── Feature/
│   └── Users/
│       ├── UserControllerTest.php (27 tests)
│       ├── UserProfileTest.php (12 tests)
│       └── ActivityLogControllerTest.php (16 tests)
└── Unit/
    └── Users/
        ├── UserServiceTest.php (19 tests)
        └── UserPolicyTest.php (29 tests)

docs/
└── users/
    ├── README.md (this file)
    ├── api-documentation.md
    ├── user-guide.md
    └── testing-guide.md
```

## API Endpoints

### User Management

- `GET /api/v1/users` - List all users (with filters)
- `POST /api/v1/users` - Create new user
- `GET /api/v1/users/{id}` - View user details
- `PUT /api/v1/users/{id}` - Update user
- `POST /api/v1/users/{id}/deactivate` - Deactivate user
- `POST /api/v1/users/{id}/activate` - Activate user
- `DELETE /api/v1/users/{id}` - Delete user

### User Profile

- `GET /api/v1/profile` - View own profile
- `PUT /api/v1/profile` - Update own profile
- `POST /api/v1/profile/change-password` - Change password
- `POST /api/v1/profile/avatar` - Upload avatar

### Activity Logs

- `GET /api/v1/users/activity-logs` - List all activity logs
- `GET /api/v1/users/{id}/activity` - View user activity
- `POST /api/v1/users/activity-logs/bulk-delete` - Bulk delete logs

### Utilities

- `GET /api/v1/users/roles/list` - Get roles list
- `GET /api/v1/users/locations/list` - Get office locations list

## Installation

### Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & npm

### Setup Steps

1. **Install Backend Dependencies:**

    ```bash
    composer install
    ```

2. **Install Frontend Dependencies:**

    ```bash
    npm install
    ```

3. **Run Database Migrations:**

    ```bash
    php artisan migrate
    ```

4. **Seed Database (optional):**

    ```bash
    php artisan db:seed --class=RoleSeeder
    php artisan db:seed --class=UserSeeder
    ```

5. **Build Frontend Assets:**

    ```bash
    npm run build
    ```

6. **Create Storage Symlink:**
    ```bash
    php artisan storage:link
    ```

## Running Tests

**Run all User Management tests:**

```bash
php artisan test tests/Feature/Users/ tests/Unit/Users/
```

**Expected Result:**

```
Tests:    103 passed (367 assertions)
Duration: ~10s
```

**Run specific test suite:**

```bash
# Feature tests only
php artisan test tests/Feature/Users/

# Unit tests only
php artisan test tests/Unit/Users/

# Specific file
php artisan test tests/Feature/Users/UserControllerTest.php
```

## Usage Examples

### Creating a User (API)

```bash
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "role_id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone_number": "+263771234567",
    "office_location": "Harare",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!"
  }'
```

### Filtering Users (API)

```bash
curl -X GET "http://localhost/api/v1/users?search=john&role_id=1&status=active" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Bulk Deleting Logs (API)

```bash
curl -X POST http://localhost/api/v1/users/activity-logs/bulk-delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "date_from": "2025-01-01",
    "date_to": "2025-01-10"
  }'
```

## Database Schema

### Users Table

| Column            | Type         | Description                  |
| ----------------- | ------------ | ---------------------------- |
| id                | bigint       | Primary key                  |
| role_id           | bigint       | Foreign key to roles         |
| name              | varchar(255) | Full name                    |
| email             | varchar(255) | Unique email (login)         |
| phone_number      | varchar(20)  | Contact number               |
| avatar_path       | varchar(255) | Avatar file path             |
| office_location   | varchar(255) | Office/location              |
| password          | varchar(255) | Bcrypt hashed password       |
| status            | varchar(20)  | active/inactive              |
| email_verified_at | timestamp    | Email verification timestamp |
| last_login_at     | timestamp    | Last login timestamp         |

### Activity Logs Table

| Column         | Type         | Description                |
| -------------- | ------------ | -------------------------- |
| id             | bigint       | Primary key                |
| user_id        | bigint       | FK to users (actor)        |
| target_user_id | bigint       | FK to users (target)       |
| activity_type  | varchar(50)  | Type of activity           |
| description    | text         | Human-readable description |
| changes        | text         | JSON of changed fields     |
| ip_address     | varchar(45)  | User IP address            |
| user_agent     | varchar(255) | Browser user agent         |
| created_at     | timestamp    | When activity occurred     |

## Security Features

### Authentication

- Laravel Sanctum token-based authentication
- Token expiration and revocation
- Password hashing with Bcrypt

### Authorization

- Laravel Policies for all actions
- Role-based access control
- Self-protection (cannot delete/deactivate self)

### Validation

- Form Request validation
- Email uniqueness checks
- Password strength requirements (min 8 chars)
- File upload validation (type, size)

### Activity Logging

- All user actions logged automatically
- Includes IP address and user agent
- Tracks changes to user data
- Immutable log entries

## Performance Optimizations

### Database

- Indexed columns: email, role_id, status
- Eager loading relationships (role)
- Pagination for large datasets
- Efficient query filtering

### File Storage

- Avatar resizing (200x200) to reduce storage
- Old avatar deletion on upload
- Public disk for fast serving
- Storage symlink for direct access

### Caching

- Roles list cached (rarely changes)
- Office locations cached
- Avatar URLs cached in resources

## Troubleshooting

### Common Issues

**Issue:** Cannot access User Management page

- **Solution:** Ensure you're logged in as Programs Manager

**Issue:** Avatar upload fails

- **Solution:** Run `php artisan storage:link` and check file permissions

**Issue:** Tests fail

- **Solution:** Ensure test database exists and `.env.testing` is configured

**Issue:** Activity logs not showing

- **Solution:** Check middleware is applied to routes

## Future Enhancements

### Planned Features

- [ ] Export users to CSV/Excel
- [ ] Import users from CSV
- [ ] Advanced activity log filtering
- [ ] Email notifications for user creation
- [ ] Two-factor authentication
- [ ] Password reset by admin
- [ ] User groups/teams

### Performance Improvements

- [ ] Redis caching for users list
- [ ] Queue email notifications
- [ ] Background avatar processing
- [ ] Database indexing optimization

## Support

### Documentation

- API Documentation: See `api-documentation.md`
- User Guide: See `user-guide.md`
- Testing Guide: See `testing-guide.md`

### Contact

- Email: support@canzim.org
- GitHub: [github.com/canzim/fintrack](https://github.com/canzim/fintrack)

## License

This module is part of CANZIM FinTrack and is licensed under the same terms as the main application.

---

**Last Updated:** January 15, 2025  
**Version:** 1.0  
**Status:** ✅ Production Ready  
**Tests:** 103 passing, 367 assertions, 100% pass rate
