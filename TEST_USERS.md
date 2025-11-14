# CANZIM FinTrack - Test User Credentials

## Test Users for Login Testing

Use these credentials to test the login system with different user roles:

### 1. Programs Manager (Full System Access)
- **Email:** `programs-manager@test.com`
- **Password:** `password123`
- **Access:** Full access to all modules (Projects, Budgets, Expenses, Users, Settings, Reports)

### 2. Finance Officer (Financial Modules)
- **Email:** `finance-officer@test.com`
- **Password:** `password123`
- **Access:** Financial modules (Projects, Budgets, Expenses, Cash Flow, Purchase Orders, Donors, Reports)

### 3. Project Officer (Project-Focused)
- **Email:** `project-officer@test.com`
- **Password:** `password123`
- **Access:** Project modules (Dashboard, Projects, Expenses, Documents)

---

## Admin User (Production)

### Programs Manager Admin
- **Email:** `admin@canzim.org.zw`
- **Password:** `canzim@2025`
- **Access:** Full system access (same as Programs Manager)

---

## Testing Instructions

1. **Start the development server:**
   ```bash
   php artisan serve
   ```

2. **Access the application:**
   - Open browser to: `http://127.0.0.1:8000`

3. **Login with test credentials:**
   - Click "Get Started" or "Sign In" button
   - Enter one of the test user emails above
   - Enter password: `password123`
   - Click "Sign In"

4. **Test role-based access:**
   - After login, you'll be redirected to `/dashboard`
   - Check the sidebar navigation - menu items will be different based on the role
   - Programs Manager sees all menu items
   - Finance Officer sees financial-related items only
   - Project Officer sees project-related items only

5. **Test logout:**
   - Click the user avatar in the top-right corner
   - Click "Logout"
   - Confirm the logout action

---

## Notes

- All test users are pre-verified (email_verified_at is set)
- All test users have status: `active`
- Phone numbers are dummy values: +263771111111, +263772222222, +263773333333
- Office location: Harare
- These test users persist across all migrations and development stages

---

**Last Updated:** November 14, 2025
