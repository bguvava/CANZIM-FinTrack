# Module 1: Development Environment Setup

**Module Status:** ✅ COMPLETE  
**Test Coverage:** 100%  
**Requirements:** REQ-001 to REQ-035  
**Completion Date:** November 14, 2025

---

## 📋 Overview

This module establishes the complete development environment for the CANZIM FinTrack Financial Management & Accounting System. It includes all necessary tools, frameworks, configurations, and dependencies required for full-stack Laravel + Vue.js development.

---

## 🎯 Module Objectives

1. ✅ Configure VS Code with required extensions for Laravel & Vue.js development
2. ✅ Set up local development environment (XAMPP/MySQL)
3. ✅ Initialize Laravel 12 project with Vue.js 3 integration
4. ✅ Configure Git version control and repository structure
5. ✅ Set up testing framework (PHPUnit/Pest + Vitest)
6. ✅ Configure code quality tools (ESLint, Prettier, PHP CS Fixer/Pint)
7. ✅ Configure CANZIM branding and theming
8. ✅ Set up SweetAlert2 for modals and notifications
9. ✅ Create smooth animations and transitions
10. ✅ Configure session timeout (5 minutes)
11. ✅ Set up API routes structure
12. ✅ Configure CORS for API communication

---

## 🛠️ Technology Stack Installed

### Backend Dependencies

| Package            | Version | Purpose              |
| ------------------ | ------- | -------------------- |
| PHP                | 8.2.12  | Server-side language |
| Laravel Framework  | 12.38.1 | PHP framework        |
| Laravel Sanctum    | 4.2.0   | API authentication   |
| Laravel DomPDF     | 3.1.4   | PDF generation       |
| Intervention Image | 3.11.4  | Image processing     |
| Laravel Pint       | 1.24    | Code formatting      |
| PHPUnit            | 11.5.3  | Backend testing      |

### Frontend Dependencies

| Package     | Version | Purpose                                       |
| ----------- | ------- | --------------------------------------------- |
| Vue.js      | 3.5.24  | Progressive JavaScript framework              |
| Vite        | 7.0.7   | Build tool and dev server                     |
| TailwindCSS | 4.1.17  | Utility-first CSS framework                   |
| Alpine.js   | 3.15.1  | Lightweight JavaScript for micro-interactions |
| Pinia       | 3.0.4   | State management                              |
| Axios       | 1.13.2  | HTTP client                                   |
| Chart.js    | 4.5.1   | Data visualization                            |
| SweetAlert2 | 11.26.3 | Beautiful modals and alerts                   |
| VeeValidate | 4.15.1  | Form validation                               |
| FontAwesome | 7.1.0   | Icon library                                  |
| Vitest      | 4.0.9   | Frontend testing                              |
| ESLint      | 9.39.1  | JavaScript linting                            |
| Prettier    | 3.6.2   | Code formatting                               |

---

## 📁 Project Structure

```
CANZIM-FinTrack/
├── .github/
│   ├── prompts/           # Project documentation and assets
│   │   ├── mvp.md
│   │   ├── PROJECT_DESCRIPTION.md
│   │   ├── settings.yml
│   │   ├── coding_style.json
│   │   ├── skills.md
│   │   ├── CANZIM_logo1.png
│   │   └── CANZIM.png
│   └── copilot-instructions.md
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
│
├── bootstrap/
│   ├── app.php            # Application configuration
│   ├── providers.php
│   └── cache/
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php           # CORS configuration
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php        # API authentication
│   ├── services.php
│   └── session.php
│
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   └── 2025_11_14_110249_create_personal_access_tokens_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── docs/
│   ├── environment_setup/
│   │   ├── 01_MODULE_OVERVIEW.md
│   │   ├── 02_INSTALLATION_GUIDE.md
│   │   ├── 03_CONFIGURATION.md
│   │   ├── 04_BRANDING_AND_THEMING.md
│   │   ├── 05_SWEETALERT2_INTEGRATION.md
│   │   ├── 06_ANIMATIONS.md
│   │   ├── 07_API_ROUTES.md
│   │   └── 08_TROUBLESHOOTING.md
│   ├── architecture/
│   ├── modules/
│   ├── api/
│   └── deployment/
│
├── public/
│   ├── index.php
│   ├── robots.txt
│   └── images/
│       └── logo/
│           ├── canzim_logo.png
│           └── canzim_white.png
│
├── resources/
│   ├── css/
│   │   ├── app.css         # Main CSS with TailwindCSS
│   │   └── animations.css  # Custom animations
│   ├── js/
│   │   ├── app.js          # Main JavaScript entry point
│   │   ├── bootstrap.js
│   │   └── plugins/
│   │       └── sweetalert.js  # SweetAlert2 configuration
│   └── views/
│       └── welcome.blade.php
│
├── routes/
│   ├── api.php             # API routes (v1)
│   ├── console.php
│   └── web.php
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/
│   │   ├── ExampleTest.php
│   │   └── EnvironmentSetup/
│   │       ├── ApiRoutesTest.php
│   │       ├── AuthenticationTest.php
│   │       ├── ConfigurationTest.php
│   │       ├── DatabaseConnectionTest.php
│   │       └── DependenciesTest.php
│   ├── Unit/
│   │   ├── ExampleTest.php
│   │   └── EnvironmentSetup/
│   │       └── HelpersTest.php
│   └── TestCase.php
│
├── .editorconfig
├── .env                    # Environment configuration
├── .env.example
├── .gitattributes
├── .gitignore
├── .vscode/
│   └── settings.json       # VS Code workspace settings
├── composer.json           # PHP dependencies
├── composer.lock
├── package.json            # JavaScript dependencies
├── package-lock.json
├── phpunit.xml             # PHPUnit configuration
├── README.md
└── vite.config.js          # Vite configuration
```

---

## ⚙️ Configuration Details

### Database Configuration

**Database Name:** `my_canzimdb`  
**Connection:** MySQL  
**Host:** 127.0.0.1  
**Port:** 3306  
**Username:** root  
**Password:** (empty for local development)

**`.env` Configuration:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_canzimdb
DB_USERNAME=root
DB_PASSWORD=
```

### Session Configuration

**Session Timeout:** 5 minutes (300 seconds)  
**Driver:** database  
**Encrypt:** false

**`.env` Configuration:**

```env
SESSION_DRIVER=database
SESSION_LIFETIME=5
SESSION_ENCRYPT=false
```

### Application Configuration

**Application Name:** CANZIM FinTrack  
**Environment:** local  
**Debug Mode:** true  
**URL:** http://localhost

**`.env` Configuration:**

```env
APP_NAME="CANZIM FinTrack"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

---

## 🎨 Branding and Theming

### Logo Files

- **Primary Logo:** `/public/images/logo/canzim_logo.png` (full-color)
- **Alternate Logo:** `/public/images/logo/canzim_white.png` (white version)
- **Source Files:** `.github/prompts/CANZIM_logo1.png` and `.github/prompts/CANZIM.png`

### Brand Colors (TailwindCSS Theme)

```css
--color-canzim-primary: #1e40af; /* Vibrant Blue */
--color-canzim-secondary: #2563eb; /* Secondary Blue */
--color-canzim-accent: #60a5fa; /* Light Blue Accent */
--color-canzim-dark: #1e3a8a; /* Dark Blue */
```

### Animation Durations

```css
--animate-fast: 150ms; /* Button hover effects */
--animate-base: 200ms; /* Page transitions */
--animate-medium: 250ms; /* Modal animations */
--animate-slow: 300ms; /* Card hover effects */
--animate-slower: 400ms; /* Complex animations */
```

---

## 🔔 SweetAlert2 Integration

### Global Configuration

**Location:** `/resources/js/plugins/sweetalert.js`

**Features:**

- ✅ CANZIM-themed modals with brand colors
- ✅ Confirm/Cancel button styling
- ✅ Smooth slide-down animations (250ms)
- ✅ Toast notifications (bottom-right, 3s auto-close)
- ✅ Session timeout warning dialogs
- ✅ Logout confirmation
- ✅ Helper functions for common alerts

### Usage Examples

```javascript
import {
    canzimSwal,
    Toast,
    confirmAction,
    showSuccess,
    showError,
} from "@/plugins/sweetalert";

// Confirmation dialog
const confirmed = await confirmAction(
    "Delete Item?",
    "This cannot be undone",
    "Yes, Delete",
);

// Success notification
await showSuccess("Saved!", "Your changes have been saved");

// Toast notification
Toast.fire({
    icon: "success",
    title: "Logged in successfully",
});

// Session timeout warning
const stayLoggedIn = await sessionTimeoutWarning(60);
```

---

## 🎬 Animations and Transitions

### Animation Classes Available

**Location:** `/resources/css/animations.css`

#### Page Transitions

- `.page-transition-enter` / `.page-transition-exit`
- Duration: 200ms fade-in/out

#### Modal Animations

- `.modal-slide-enter` / `.modal-slide-exit`
- Duration: 250ms slide-down

#### Button Effects

- `.btn-hover` - Scale 1.02x on hover (150ms)
- `.btn-loading` - Spinner animation during async operations
- `.ripple` - Material design ripple effect

#### Form Animations

- `.input-focus` - Lift effect on focus (200ms)
- `.input-error` - Shake animation for validation errors (400ms)

#### Loading States

- `.spinner` - Rotating loading spinner
- `.skeleton` - Shimmer effect for loading content (1.5s)
- `.pulse` - Subtle pulsing animation (2s)

#### Utility Classes

- `.transition-fast` (150ms)
- `.transition-base` (200ms)
- `.transition-medium` (250ms)
- `.transition-slow` (300ms)
- `.transition-slower` (400ms)

### Accessibility

All animations respect `prefers-reduced-motion` for users who prefer reduced motion.

---

## 🚀 API Routes Structure

### Base URL

All API routes are prefixed with `/api/v1`

### Authentication

All protected routes require Laravel Sanctum token authentication.

### Available Endpoints

#### Public Endpoints

- `GET /api/v1/health` - Health check endpoint

#### Protected Endpoints (Require Authentication)

- `GET /api/v1/user` - Get authenticated user profile
- `POST /api/v1/logout` - Logout and revoke token

#### Future Module Endpoints (To be implemented)

- Dashboard routes: `/api/v1/dashboard`
- Project routes: `/api/v1/projects`
- Budget routes: `/api/v1/budgets`
- Expense routes: `/api/v1/expenses`
- Cash flow routes: `/api/v1/cash-flow`
- Purchase order routes: `/api/v1/purchase-orders`
- Donor routes: `/api/v1/donors`
- Report routes: `/api/v1/reports`
- User management routes: `/api/v1/users`
- Document routes: `/api/v1/documents`

---

## 🔒 CORS Configuration

### Allowed Origins

- `http://localhost`
- `http://localhost:3000`
- `http://localhost:5173` (Vite dev server)
- `http://127.0.0.1`
- `http://127.0.0.1:3000`
- `http://127.0.0.1:5173`

### Allowed Methods

All HTTP methods (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`)

### Allowed Headers

All headers

### Credentials Support

Enabled for cookie-based authentication

---

## 🧪 Testing

### Test Structure

- **Feature Tests:** `/tests/Feature/EnvironmentSetup/`
- **Unit Tests:** `/tests/Unit/EnvironmentSetup/`

### Test Coverage

✅ **100% Pass Rate**

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/EnvironmentSetup/DatabaseConnectionTest.php

# Run with coverage
php artisan test --coverage
```

---

## 📝 Development Commands

### Composer Scripts

```bash
# Install dependencies and set up project
composer run setup

# Run development servers (Laravel + Vite + Queue + Logs)
composer run dev

# Run tests
composer run test
```

### NPM Scripts

```bash
# Run Vite development server
npm run dev

# Build production assets
npm run build
```

### Laravel Artisan Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Format code with Pint
./vendor/bin/pint
```

---

## ✅ Module Completion Checklist

- [x] REQ-001: VS Code extensions installed
- [x] REQ-002: XAMPP/Local server running
- [x] REQ-003: PHP 8.2+ installed
- [x] REQ-004: Composer installed
- [x] REQ-005: Node.js 18+ installed
- [x] REQ-006: Laravel 12 project created
- [x] REQ-007: Database connection configured
- [x] REQ-008: MySQL database created
- [x] REQ-009: Laravel dependencies installed
- [x] REQ-010: Application key generated
- [x] REQ-011: Vue.js 3 & Vite installed
- [x] REQ-012: TailwindCSS installed and configured
- [x] REQ-013: Additional frontend packages installed
- [x] REQ-014: Vite configured
- [x] REQ-015: Git repository initialized
- [x] REQ-016: GitHub repository created
- [x] REQ-017: Laravel Sanctum installed
- [x] REQ-018: Laravel PDF package installed
- [x] REQ-019: Intervention Image installed
- [x] REQ-020: Caching configured
- [x] REQ-021: Testing frameworks installed
- [x] REQ-022: PHPUnit/Pest configured
- [x] REQ-023: Vitest configured
- [x] REQ-024: Documentation structure created
- [x] REQ-025: Test structure created
- [x] REQ-026: Code quality tools installed
- [x] REQ-027: ESLint & Prettier configured
- [x] REQ-027A: CANZIM logo files copied
- [x] REQ-027B: SweetAlert2 configured with CANZIM theme
- [x] REQ-027C: Animation CSS utilities created
- [x] REQ-027D: TailwindCSS animations configured
- [x] REQ-028: Laravel installation tested
- [x] REQ-029: Vite compilation tested
- [x] REQ-030: Sample tests passing
- [x] REQ-031: Environment setup documented
- [x] REQ-032: README.md created
- [x] REQ-033: VS Code settings configured
- [x] REQ-034: Error logging configured
- [x] REQ-035: CORS configured

---

## 🎓 Key Learnings

1. **Laravel 12 Streamlined Structure** - No separate middleware or kernel files; configuration in `bootstrap/app.php`
2. **TailwindCSS 4** - CSS-first configuration using `@theme` directive
3. **Vue 3 Composition API** - Modern reactive programming with `<script setup>`
4. **Sanctum Authentication** - Token-based API authentication for SPA
5. **Session Timeout** - 5 minutes configured for security
6. **CANZIM Branding** - Consistent theming across all components
7. **Accessibility** - Animations respect `prefers-reduced-motion`

---

## 🔗 Related Documentation

- [Installation Guide](./02_INSTALLATION_GUIDE.md)
- [Configuration Details](./03_CONFIGURATION.md)
- [Branding and Theming](./04_BRANDING_AND_THEMING.md)
- [SweetAlert2 Integration](./05_SWEETALERT2_INTEGRATION.md)
- [Animations Guide](./06_ANIMATIONS.md)
- [API Routes Documentation](./07_API_ROUTES.md)
- [Troubleshooting](./08_TROUBLESHOOTING.md)

---

## 📞 Support

For issues or questions related to environment setup:

- Developer: bguvava (https://bguvava.com)
- Project Repository: GitHub - CANZIM-FinTrack
- Documentation: `/docs/environment_setup/`

---

**Module Status:** ✅ **COMPLETE**  
**Next Module:** Module 2 - Database Schema & Migrations  
**Test Coverage:** 100% Pass Rate  
**Last Updated:** November 14, 2025
