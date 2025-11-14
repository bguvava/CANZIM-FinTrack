# User Management System - User Guide

## Overview

The User Management System allows Programs Managers to manage all system users, including creating new accounts, updating user information, activating/deactivating accounts, and monitoring user activity.

## Table of Contents

1. [Getting Started](#getting-started)
2. [User Roles & Permissions](#user-roles--permissions)
3. [Managing Users](#managing-users)
4. [User Profile Management](#user-profile-management)
5. [Activity Logs](#activity-logs)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Accessing User Management

1. Log in to CANZIM FinTrack with your Programs Manager account
2. Navigate to **Dashboard** → **Users** from the sidebar menu
3. The User Management page will display all system users

> **Note:** Only Programs Managers have access to the full User Management system. Other roles can only view and edit their own profiles.

---

## User Roles & Permissions

### Programs Manager

**Full Access** - Can perform all actions:

- View all users
- Create new users
- Edit any user (except cannot delete/deactivate self)
- Deactivate/activate users
- Delete users
- View all activity logs
- Bulk delete activity logs

### Finance Officer

**Limited Access:**

- View own profile
- Edit own profile
- Change own password
- Upload own avatar
- View own activity logs

### Project Officer

**Limited Access:**

- View own profile
- Edit own profile
- Change own password
- Upload own avatar
- View own activity logs

---

## Managing Users

### Viewing Users List

The Users page displays a table with all system users, showing:

- **User Information:** Name, email, avatar
- **Role:** User's assigned role (color-coded badge)
- **Office Location:** Where the user is based
- **Status:** Active (green) or Inactive (red)
- **Last Login:** When the user last accessed the system
- **Actions:** Quick action buttons

### Searching & Filtering

**Search Box:**

- Type in name or email to search
- Results update automatically (debounced 300ms)

**Filters:**

1. **Role Filter** - Select specific role to view
2. **Status Filter** - Filter by Active/Inactive status
3. **Office Location Filter** - Filter by office location

**Clear Filters Button:**

- Click "Clear Filters" to reset all filters and view all users

### Creating a New User

1. Click the **"Add User"** button (top right)
2. Fill in the required information:
    - **Role:** Select from dropdown (Programs Manager, Finance Officer, Project Officer)
    - **Name:** Full name of the user
    - **Email:** Unique email address (used for login)
    - **Phone Number:** Optional contact number
    - **Office Location:** Optional location field
    - **Password:** Minimum 8 characters (must include letters and numbers)
    - **Confirm Password:** Must match the password
3. Click **"Create User"**
4. The new user will receive an email with their login credentials

**Validation Rules:**

- Email must be unique in the system
- Password must be at least 8 characters
- All required fields must be filled

**Success:**

- Green success message appears
- User list refreshes automatically
- New user appears in the table

### Viewing User Details

1. Click the **eye icon** (👁️) in the Actions column
2. A modal displays:
    - Full user information
    - Avatar (if uploaded)
    - Role and permissions
    - Account status
    - Last login timestamp
3. Click **"Edit"** button in modal to edit the user
4. Click **"Close"** or click outside modal to dismiss

### Editing a User

1. Click the **edit icon** (✏️) in the Actions column
    - OR click "Edit" from the View User modal
2. Update any of the following fields:
    - Name
    - Email
    - Phone Number
    - Office Location
    - Role (changes user permissions)
3. Click **"Update User"**
4. Changes are saved and activity is logged

**Important:**

- Changing a user's role immediately affects their access
- Email changes must still be unique
- You can edit any user except you cannot delete or deactivate yourself

### Deactivating a User

**Purpose:** Temporarily disable a user account without deleting it

**Steps:**

1. Click the **ban icon** (🚫) for the active user
2. Confirm the action in the popup dialog
3. User status changes to "Inactive"
4. User is immediately logged out from all sessions
5. User cannot log in until reactivated

**Effect:**

- User account is preserved
- All user data remains intact
- All active sessions are terminated
- User receives "Account deactivated" message on login attempt

**Important:** You cannot deactivate yourself!

### Activating a User

**Purpose:** Re-enable a previously deactivated account

**Steps:**

1. Find the inactive user (red "Inactive" badge)
2. Click the **check icon** (✓) for the inactive user
3. Confirm the action
4. User status changes to "Active"
5. User can now log in again

### Deleting a User

**Purpose:** Permanently remove a user account (soft delete)

**Steps:**

1. Click the **trash icon** (🗑️) in the Actions column
2. Confirm the deletion in the popup dialog
3. User is soft-deleted from the system
4. All active sessions are terminated

**Important:**

- This action soft-deletes the user (data preserved in database)
- User data remains for audit purposes
- User cannot log in and won't appear in user lists
- You cannot delete yourself!
- Consider deactivating instead of deleting for temporary removals

### Pagination

**Navigation:**

- Use **Previous** and **Next** buttons to navigate pages
- Click page numbers to jump to specific pages
- Shows X-Y of Z results at bottom of table

**Settings:**

- Default: 25 users per page
- Automatically updates when filters are applied

---

## User Profile Management

### Viewing Your Profile

**All Users:**

1. Click your avatar/name in the top-right corner
2. Select **"My Profile"** from the dropdown
    - OR click **"Profile"** in the sidebar menu
3. View your complete profile information

### Editing Your Profile

**Fields You Can Edit:**

- **Name:** Your display name
- **Email:** Login email (must be unique)
- **Phone Number:** Contact number
- **Office Location:** Your office/location

**Steps:**

1. Navigate to your profile page
2. Click **"Edit Profile"** button
3. Update the fields you want to change
4. Click **"Save Changes"**
5. Profile is updated and activity is logged

### Changing Your Password

**Security Best Practices:**

- Change password every 90 days
- Use strong passwords (8+ characters, mix of letters/numbers/symbols)
- Don't reuse old passwords

**Steps:**

1. Go to your profile page
2. Click **"Change Password"** button
3. Enter:
    - **Current Password:** Your existing password
    - **New Password:** Your new password (min 8 characters)
    - **Confirm Password:** Re-enter new password
4. Click **"Change Password"**
5. All other sessions are logged out automatically
6. You remain logged in on current device

**Important:** All your other active sessions will be terminated for security.

### Uploading an Avatar

**Supported Formats:**

- JPG, JPEG, PNG
- Maximum file size: 2MB
- Image is automatically resized to 200x200 pixels

**Steps:**

1. Go to your profile page
2. Click **"Upload Avatar"** or click on your current avatar
3. Select an image file from your device
4. Image is uploaded and processed
5. Old avatar is automatically deleted
6. New avatar appears immediately

**Tips:**

- Use square images for best results
- High-resolution images are resized automatically
- Avatar appears in sidebar, header, and throughout the system

---

## Activity Logs

### Viewing All Activity Logs

**Access:** Programs Managers only

**Steps:**

1. Navigate to **Dashboard** → **Activity Logs** from sidebar
2. View complete system activity timeline
3. See all user actions across the system

**Information Displayed:**

- **User:** Who performed the action
- **Activity Type:** Type of action (color-coded)
- **Description:** What was done
- **Changes:** Fields that were modified (if applicable)
- **Date & Time:** When the action occurred

### Filtering Activity Logs

**Available Filters:**

1. **User Filter:**
    - Select specific user to view their actions
    - Shows all users in dropdown

2. **Activity Type Filter:**
    - User Created
    - User Updated
    - User Deactivated
    - User Activated
    - User Deleted
    - Password Changed
    - Profile Updated
    - Avatar Uploaded
    - Logs Bulk Deleted

3. **Date Range Filter:**
    - **Date From:** Start date
    - **Date To:** End date
    - Shows activities within the date range

**Using Filters:**

- Select one or more filters
- Results update automatically
- Click "Clear Filters" to reset all filters

### Viewing Your Own Activity

**All Users:**

1. Go to your profile page
2. Click **"View My Activity"** tab/button
3. See your personal activity history
4. Filters:
    - Activity type
    - Date range

### Bulk Deleting Activity Logs

**Purpose:** Remove old logs to maintain database performance

**Access:** Programs Managers only

**Steps:**

1. Click **"Bulk Delete"** button (top right of Activity Logs page)
2. Select date range:
    - **Date From:** Start date
    - **Date To:** End date
3. Click **"Delete Logs"**
4. Confirm the action
5. All logs within the date range are permanently deleted
6. A new activity log is created recording the bulk deletion

**Important:**

- This action cannot be undone
- Be careful with date selection
- Consider exporting logs before deletion
- The bulk deletion itself is logged

---

## Best Practices

### User Account Management

1. **Regular Audits:**
    - Review user list monthly
    - Deactivate accounts for users who have left
    - Update roles as staff responsibilities change

2. **Security:**
    - Create strong passwords for new users
    - Instruct users to change default passwords immediately
    - Deactivate (don't delete) accounts of departing staff for audit trail

3. **Role Assignment:**
    - Assign the least privilege necessary
    - Only assign Programs Manager role to authorized personnel
    - Review role assignments quarterly

4. **Office Locations:**
    - Keep office locations consistent
    - Use standardized location names
    - Update locations when staff relocate

### Activity Logs Management

1. **Regular Monitoring:**
    - Check activity logs weekly for unusual patterns
    - Investigate failed login attempts
    - Monitor user creation and role changes

2. **Log Retention:**
    - Keep logs for at least 12 months
    - Bulk delete logs older than 12 months
    - Export important logs before bulk deletion

3. **Compliance:**
    - Use activity logs for audit purposes
    - Document major account changes
    - Retain evidence of authorization for sensitive actions

---

## Troubleshooting

### Common Issues

**Issue:** Cannot create user - "Email already exists"

- **Solution:** Email addresses must be unique. Check if user already exists or use a different email.

**Issue:** Cannot deactivate or delete a user

- **Solution:** You cannot deactivate or delete yourself. Have another Programs Manager perform the action.

**Issue:** New user didn't receive login credentials

- **Solution:** Check spam folder, verify email address is correct, or have the user use "Forgot Password" feature.

**Issue:** Activity logs not showing after performing action

- **Solution:** Refresh the page, check your filters, ensure you're on the correct page number.

**Issue:** Cannot change password - "Current password is incorrect"

- **Solution:** Verify you're entering your current password correctly. Use "Forgot Password" if you don't remember it.

**Issue:** Avatar upload fails

- **Solution:** Check file size (<2MB), ensure it's a JPG/PNG format, try a different image.

**Issue:** Cannot access User Management page

- **Solution:** Only Programs Managers can access this page. Contact your administrator if you need access.

### Error Messages

**"This action is unauthorized"**

- You don't have permission to perform this action
- Check your role and permissions
- Contact a Programs Manager for assistance

**"Validation failed"**

- One or more form fields have errors
- Check error messages below each field
- Correct the errors and try again

**"User not found"**

- The user may have been deleted
- Try refreshing the page
- Check if you're looking at the correct user ID

**"Too many requests"**

- You've exceeded the rate limit
- Wait 60 seconds before trying again
- Contact support if issue persists

### Getting Help

**Contact Support:**

- **Email:** support@canzim.org
- **Phone:** +263 XX XXXX XXX
- **Office Hours:** Monday - Friday, 8:00 AM - 5:00 PM CAT

**Documentation:**

- API Documentation: `docs/users/api-documentation.md`
- Testing Guide: `docs/users/testing-guide.md`

**Reporting Bugs:**

- GitHub Issues: [github.com/canzim/fintrack/issues](https://github.com/canzim/fintrack/issues)
- Include: Screenshots, steps to reproduce, error messages

---

## Appendix

### Keyboard Shortcuts

| Shortcut | Action           |
| -------- | ---------------- |
| Ctrl + K | Focus search bar |

### Activity Type Reference

| Type              | Description                      | Who Can Perform  |
| ----------------- | -------------------------------- | ---------------- |
| user_created      | New user account created         | Programs Manager |
| user_updated      | User information modified        | Programs Manager |
| user_deactivated  | User account deactivated         | Programs Manager |
| user_activated    | User account reactivated         | Programs Manager |
| user_deleted      | User account deleted             | Programs Manager |
| password_changed  | User password changed            | All users        |
| profile_updated   | User profile information updated | All users        |
| avatar_uploaded   | User avatar image uploaded       | All users        |
| logs_bulk_deleted | Activity logs bulk deleted       | Programs Manager |

---

**Last Updated:** January 15, 2025  
**Version:** 1.0  
**Module:** User Management System (Module 4)
