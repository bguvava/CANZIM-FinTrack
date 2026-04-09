# CI/CD Deployment Guide - CANZIM-FinTrack

## Overview
This document outlines the Continuous Integration and Continuous Deployment (CI/CD) pipeline for the CANZIM-FinTrack application.

## Architecture

```
┌─────────────────┐
│ Local Development│
│  - Code changes │
│  - Local testing│
└────────┬────────┘
         │
         ├─ git push
         ↓
┌─────────────────┐
│   GitHub Repo   │
│ (Source Control)│
└────────┬────────┘
         │
         ├─ Triggers
         ↓
┌─────────────────┐
│ GitHub Actions  │
│   (CI Pipeline) │
├─────────────────┤
│ 1. Code Quality │
│ 2. Tests        │
│ 3. Security     │
│ 4. Build        │
└────────┬────────┘
         │
         ├─ On Success
         ↓
┌─────────────────┐
│ Staging Deploy  │
│  (develop branch)│
└────────┬────────┘
         │
         ├─ Manual Approval
         ↓
┌─────────────────┐
│Production Deploy│
│ (main/tags)     │
├─────────────────┤
│ 1. Backup       │
│ 2. Deploy       │
│ 3. Migrate      │
│ 4. Verify       │
│ 5. Monitor      │
└─────────────────┘
```

## Workflows

### 1. CI Workflow (`ci.yml`)
**Triggers:** Push to any branch, Pull Requests

**Jobs:**
- **Code Quality Checks**
  - PHP code style (Laravel Pint)
  - Validate composer.json
  
- **Frontend Quality Checks**
  - JavaScript/Vue linting (ESLint)
  - Build frontend assets
  
- **Tests**
  - PHPUnit tests with coverage (min 80%)
  - Parallel test execution
  
- **Security Scanning**
  - Composer audit for vulnerabilities
  - Dependency review

**Success Criteria:** All jobs must pass

### 2. Staging Workflow (`staging.yml`)
**Triggers:** Push to `develop` branch

**Purpose:** Automated deployment to staging environment for testing

**Jobs:**
- Run full test suite
- Build production assets
- Deploy to staging server (when configured)

### 3. Production Workflow (`cd-production.yml`)
**Triggers:** 
- Manual workflow dispatch (requires "DEPLOY" confirmation)
- Push tags matching `v*.*.*` (semantic versioning)

**Jobs:**
1. **Verify Deployment Prerequisites**
   - Validate deployment confirmation
   - Run full test suite
   
2. **Deploy to Production**
   - Setup SSH connection
   - Build frontend assets
   - Create deployment package
   - Upload to server
   - Execute deployment script
   - Run post-deployment checks
   
3. **Post-Deployment**
   - Verify application health
   - Send notifications
   
4. **Rollback** (on failure)
   - Restore from backup
   - Rollback migrations
   - Notify team

## Deployment Script

The production deployment script (`deploy-production.sh`) performs the following steps:

1. **Pre-deployment Checks**
   - Verify deployment package exists
   - Verify application directory exists

2. **Backup**
   - Create backup of current version
   - Backup database (if configured)

3. **Maintenance Mode**
   - Enable Laravel maintenance mode
   - Show maintenance page to users

4. **Deployment**
   - Extract deployment package
   - Install Composer dependencies (production only)
   - Run database migrations
   - Optimize application (config, routes, views)
   - Set correct file permissions
   - Restart services (queue workers, etc.)

5. **Verification**
   - Check Laravel version
   - Verify database connectivity
   - Run health checks

6. **Go Live**
   - Disable maintenance mode
   - Application is now live

7. **Cleanup**
   - Remove deployment package
   - Clean up temporary files

## Required GitHub Secrets

Configure these secrets in your GitHub repository settings:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SSH_PRIVATE_KEY` | Private SSH key for server access | Contents of `~/.ssh/id_rsa` |
| `SSH_USER` | SSH username | `blaxi2540` |
| `SERVER_IP` | Production server IP address | `158.220.103.133` |
| `DB_PASSWORD` | Production database password | (secure password) |
| `APP_KEY` | Laravel application key | `base64:...` |

## Environment Configuration

### Production `.env` File
Create this on the server at `/home/blaxium.com/canzim.blaxium.com/.env`:

```bash
APP_NAME="CANZIM FinTrack"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://canzim.blaxium.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=canzim_fintrack
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@canzim.blaxium.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database

# Cache
CACHE_STORE=database

# Filesystem
FILESYSTEM_DISK=local
```

## Deployment Process

### First-Time Setup

1. **Setup SSH Access**
```bash
# On your local machine, generate SSH key pair
ssh-keygen -t ed25519 -C "github-actions-canzim"

# Copy public key to server
ssh-copy-id -i ~/.ssh/id_ed25519.pub blaxi2540@158.220.103.133

# Add private key to GitHub Secrets
cat ~/.ssh/id_ed25519
# Copy output to GitHub Secrets as SSH_PRIVATE_KEY
```

2. **Configure GitHub Secrets**
   - Go to GitHub repo → Settings → Secrets and variables → Actions
   - Add all required secrets listed above

3. **Initial Server Setup**
```bash
# SSH to server
ssh blaxi2540@158.220.103.133

# Navigate to application directory
cd /home/blaxium.com/canzim.blaxium.com

# Clone repository (first time only)
git clone https://github.com/bguvava/CANZIM-FinTrack.git .

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Create .env file
cp .env.example .env
nano .env  # Edit with production settings

# Generate application key
php artisan key:generate

# Create database
mysql -u root -p
CREATE DATABASE canzim_fintrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'canzim_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON canzim_fintrack.* TO 'canzim_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 775 storage bootstrap/cache

# Build frontend assets (locally then upload, or on server)
npm install
npm run build

# Optimize application
php artisan optimize
```

### Subsequent Deployments

#### Option 1: Automated (Recommended)
1. Push changes to `main` branch or create a version tag
2. Go to GitHub Actions
3. Run "CD - Deploy to Production" workflow
4. Type "DEPLOY" in confirmation field
5. Monitor deployment progress
6. Verify at https://canzim.blaxium.com

#### Option 2: Manual
```bash
# SSH to server
ssh blaxi2540@158.220.103.133

# Run deployment commands
cd /home/blaxium.com/canzim.blaxium.com
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan up
```

## Rollback Procedure

### Automatic Rollback
If deployment fails, the CD workflow automatically:
1. Detects failure
2. Restores from backup
3. Rolls back last migration
4. Re-optimizes application
5. Brings application back online

### Manual Rollback
```bash
# SSH to server
ssh blaxi2540@158.220.103.133

cd /home/blaxium.com

# Restore from backup
rsync -av --delete canzim.blaxium.com.backup/ canzim.blaxium.com/

cd canzim.blaxium.com

# Rollback migration (if needed)
php artisan migrate:rollback --step=1

# Optimize
php artisan optimize

# Bring online
php artisan up
```

## Monitoring and Health Checks

### Application Health Endpoint
Create a health check endpoint:

```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
    ]);
});
```

### Monitor These Metrics:
- Application uptime
- Response time
- Error rate
- Database connectivity
- Disk usage
- Memory usage

### Logs
```bash
# Laravel logs
tail -f /home/blaxium.com/canzim.blaxium.com/storage/logs/laravel.log

# Web server logs
tail -f /home/blaxium.com/logs/blaxium.com.error_log
tail -f /home/blaxium.com/logs/blaxium.com.access_log
```

## Troubleshooting

### Common Issues

**Issue: Deployment fails with SSH connection error**
- Verify SSH key is correctly added to GitHub Secrets
- Check server firewall allows SSH connections
- Verify SSH user has correct permissions

**Issue: Migration fails**
- Check database credentials in `.env`
- Verify database user has migration permissions
- Check for conflicting migrations

**Issue: 500 Internal Server Error after deployment**
- Check Laravel logs: `storage/logs/laravel.log`
- Verify `.env` file configuration
- Run `php artisan config:clear`
- Check file permissions on `storage/` and `bootstrap/cache/`

**Issue: Assets not loading**
- Verify `npm run build` completed successfully
- Check `public/build/` directory exists
- Clear browser cache
- Check web server configuration

## Best Practices

1. **Always test locally first** - Ensure changes work on local environment
2. **Use feature branches** - Develop features in separate branches
3. **Write tests** - Maintain test coverage above 80%
4. **Review PRs** - Code review before merging to main
5. **Use semantic versioning** - Tag releases as v1.0.0, v1.1.0, etc.
6. **Monitor after deployment** - Watch logs and metrics post-deploy
7. **Keep backups** - Regular database and file backups
8. **Document changes** - Update CHANGELOG.md
9. **Security first** - Never commit secrets, keep dependencies updated
10. **Deploy during low-traffic periods** - Minimize user impact

## Git Workflow

```
feature/new-feature → develop → main → production
                         ↓        ↓
                     staging   production
```

1. Create feature branch from `develop`
2. Develop and test feature
3. Create PR to `develop`
4. CI runs automatically
5. After review, merge to `develop`
6. Staging deployment happens automatically
7. Test on staging
8. Create PR from `develop` to `main`
9. After approval, merge to `main`
10. Tag release: `git tag v1.0.0`
11. Push tag: `git push origin v1.0.0`
12. Production deployment triggered

## Support

For deployment issues:
1. Check GitHub Actions logs
2. Check server logs
3. Review this documentation
4. Contact system administrator

---

**Last Updated:** April 9, 2026  
**Version:** 1.0.0
