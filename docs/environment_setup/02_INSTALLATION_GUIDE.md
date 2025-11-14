# Installation Guide - CANZIM FinTrack Development Environment

**Document Version:** 1.0.0  
**Last Updated:** November 14, 2025  
**Target Audience:** Developers setting up the CANZIM FinTrack project

---

## 📋 Prerequisites

Before starting the installation, ensure you have the following installed on your system:

### Required Software

| Software         | Minimum Version | Download Link                                        |
| ---------------- | --------------- | ---------------------------------------------------- |
| PHP              | 8.2.12          | [php.net](https://www.php.net/downloads)             |
| Composer         | 2.x             | [getcomposer.org](https://getcomposer.org/download/) |
| Node.js          | 18.x            | [nodejs.org](https://nodejs.org/)                    |
| MySQL            | 8.0+            | [mysql.com](https://dev.mysql.com/downloads/)        |
| Git              | 2.x             | [git-scm.com](https://git-scm.com/downloads)         |
| XAMPP (Optional) | 8.2+            | [apachefriends.org](https://www.apachefriends.org/)  |

### System Requirements

- **Operating System:** Windows 10/11, macOS, or Linux
- **RAM:** Minimum 4GB (8GB recommended)
- **Disk Space:** Minimum 2GB free space
- **Internet Connection:** Required for downloading dependencies

---

## 🚀 Installation Steps

### Step 1: Install XAMPP (Recommended for Windows)

1. Download XAMPP 8.2+ from [apachefriends.org](https://www.apachefriends.org/)
2. Run the installer and select components:
    - ✅ Apache
    - ✅ MySQL
    - ✅ PHP
    - ✅ phpMyAdmin
3. Install to `C:\xampp` (default location)
4. Start Apache and MySQL from XAMPP Control Panel

**Verification:**

```bash
# Check PHP version
php -v
# Should output: PHP 8.2.12 or higher
```

### Step 2: Install Composer

1. Download Composer from [getcomposer.org](https://getcomposer.org/download/)
2. Run the installer and follow the prompts
3. Select the PHP executable from XAMPP: `C:\xampp\php\php.exe`

**Verification:**

```bash
composer --version
# Should output: Composer version 2.x.x
```

### Step 3: Install Node.js and NPM

1. Download Node.js LTS from [nodejs.org](https://nodejs.org/)
2. Run the installer (NPM is included)
3. Restart your terminal after installation

**Verification:**

```bash
node -v
# Should output: v18.x.x or higher

npm -v
# Should output: 9.x.x or higher
```

### Step 4: Install Git

1. Download Git from [git-scm.com](https://git-scm.com/downloads)
2. Run the installer with default options
3. Configure Git with your information:

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

**Verification:**

```bash
git --version
# Should output: git version 2.x.x
```

### Step 5: Clone the Project Repository

```bash
# Navigate to XAMPP htdocs directory
cd C:\xampp\htdocs

# Clone the repository (replace with actual repository URL)
git clone https://github.com/yourusername/CANZIM-FinTrack.git

# Navigate to project directory
cd CANZIM-FinTrack
```

### Step 6: Install PHP Dependencies

```bash
# Install all Composer dependencies
composer install

# This will install:
# - Laravel 12.38.1
# - Laravel Sanctum 4.2.0
# - DomPDF 3.1.4
# - Intervention Image 3.11.4
# - PHPUnit 11.5.3
# - And all other PHP packages
```

**Expected Output:**

```
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
...
Generating optimized autoload files
...
Packages successfully installed
```

### Step 7: Install JavaScript Dependencies

```bash
# Install all NPM dependencies
npm install

# This will install:
# - Vue.js 3.5.24
# - TailwindCSS 4.1.17
# - Pinia 3.0.4
# - SweetAlert2 11.26.3
# - Chart.js 4.5.1
# - Vitest 4.0.9
# - And all other JavaScript packages
```

**Expected Output:**

```
added 500+ packages in 30s
```

### Step 8: Configure Environment File

```bash
# Copy the example environment file
copy .env.example .env
# On macOS/Linux: cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
APP_NAME="CANZIM FinTrack"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_canzimdb
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=5
```

### Step 9: Generate Application Key

```bash
php artisan key:generate
```

**Expected Output:**

```
Application key set successfully.
```

### Step 10: Create MySQL Database

**Option A: Using phpMyAdmin**

1. Open http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Database name: `my_canzimdb`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Option B: Using MySQL Command Line**

```bash
# Access MySQL
mysql -u root -p

# Create database
CREATE DATABASE my_canzimdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Verify
SHOW DATABASES;

# Exit
EXIT;
```

### Step 11: Run Database Migrations

```bash
php artisan migrate
```

**Expected Output:**

```
Migration table created successfully.
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table (XX.XXms)
Migrating: 0001_01_01_000001_create_cache_table
Migrated:  0001_01_01_000001_create_cache_table (XX.XXms)
...
```

### Step 12: Copy CANZIM Logo Files

The logo files should already be copied during setup, but verify:

```bash
# Check if logo directory exists
ls public/images/logo

# Should show:
# canzim_logo.png
# canzim_white.png
```

If files are missing:

```bash
# Create directory
New-Item -ItemType Directory -Force -Path "public/images/logo"

# Copy files
Copy-Item ".github/prompts/CANZIM_logo1.png" "public/images/logo/canzim_logo.png"
Copy-Item ".github/prompts/CANZIM.png" "public/images/logo/canzim_white.png"
```

### Step 13: Build Frontend Assets

```bash
# Build assets for production
npm run build

# Or run development server with hot reload
npm run dev
```

**Expected Output (dev):**

```
VITE v7.0.7  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
```

### Step 14: Start Laravel Development Server

Open a new terminal and run:

```bash
php artisan serve
```

**Expected Output:**

```
Starting Laravel development server: http://127.0.0.1:8000
[Press Ctrl+C to quit]
```

### Step 15: Verify Installation

Open your browser and navigate to:

- **Laravel:** http://localhost:8000
- **API Health Check:** http://localhost:8000/api/v1/health

You should see:

- Laravel welcome page
- API health check JSON response

---

## 🧪 Verify Installation with Tests

Run the test suite to ensure everything is working:

```bash
php artisan test
```

**Expected Output:**

```
PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

PASS  Tests\Feature\EnvironmentSetup\DatabaseConnectionTest
✓ database connection is successful
✓ database name is correct

...

Tests:    31 passed (60 assertions)
Duration: X.XXs
```

---

## 🛠️ Development Tools Setup

### VS Code Extensions

Install recommended extensions:

1. **Laravel Extension Pack** (amiralizadeh9480.laravel-extra-intellisense)
2. **Volar** (Vue.volar)
3. **ESLint** (dbaeumer.vscode-eslint)
4. **Prettier** (esbenp.prettier-vscode)
5. **PHP Intelephense** (bmewburn.vscode-intelephense-client)
6. **Tailwind CSS IntelliSense** (bradlc.vscode-tailwindcss)
7. **GitLens** (eamodio.gitlens)

### VS Code Workspace Settings

The project includes `.vscode/settings.json` with recommended settings. VS Code will automatically load them.

---

## 🚀 Quick Start Commands

Once installation is complete, use these commands for daily development:

### Using Composer Scripts (Recommended)

```bash
# Start all development servers (Laravel + Vite + Queue + Logs)
composer run dev

# Run tests
composer run test
```

### Manual Commands

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite development server
npm run dev

# Terminal 3 (Optional): Queue worker
php artisan queue:listen

# Terminal 4 (Optional): Logs
php artisan pail
```

---

## ⚠️ Troubleshooting

### Issue: "composer: command not found"

**Solution:**

- Restart your terminal after installing Composer
- Add Composer to PATH manually if needed
- On Windows: `C:\ProgramData\ComposerSetup\bin`

### Issue: "Class 'PDO' not found"

**Solution:**

- Enable PDO extension in `php.ini`
- Uncomment: `extension=pdo_mysql`
- Restart Apache

### Issue: "SQLSTATE[HY000] [1045] Access denied"

**Solution:**

- Verify MySQL username and password in `.env`
- Default XAMPP credentials: `root` with empty password
- Check if MySQL service is running

### Issue: "Vite: Port 5173 already in use"

**Solution:**

```bash
# Kill process on port 5173
npx kill-port 5173

# Or change port in vite.config.js
server: {
  port: 3000
}
```

### Issue: "npm install" fails

**Solution:**

```bash
# Clear NPM cache
npm cache clean --force

# Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

### Issue: "Migration not found"

**Solution:**

```bash
# Clear config cache
php artisan config:clear

# Rerun migrations
php artisan migrate:fresh
```

---

## 📝 Next Steps

After successful installation:

1. ✅ Review the [Configuration Guide](./03_CONFIGURATION.md)
2. ✅ Read [Branding and Theming](./04_BRANDING_AND_THEMING.md)
3. ✅ Explore [SweetAlert2 Integration](./05_SWEETALERT2_INTEGRATION.md)
4. ✅ Check [API Routes Documentation](./07_API_ROUTES.md)
5. ✅ Start developing Module 2: Database Schema & Migrations

---

## 📞 Support

For installation issues:

- Developer: bguvava (https://bguvava.com)
- Documentation: `/docs/environment_setup/`
- Troubleshooting: `./08_TROUBLESHOOTING.md`

---

**Installation Complete!** 🎉  
You're now ready to start developing CANZIM FinTrack.
