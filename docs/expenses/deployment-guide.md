# Expense Management Module - Deployment Guide

## Pre-Deployment Checklist

### System Requirements

- ✅ PHP 8.2+ installed
- ✅ MySQL 8.0+ running
- ✅ Node.js 18+ installed
- ✅ Composer installed
- ✅ Web server (Apache/Nginx) configured
- ✅ SSL certificate (production)

### Application Requirements

- ✅ Laravel 12 application running
- ✅ Authentication system active (Sanctum)
- ✅ User roles seeded (Programs Manager, Finance Officer, Project Officer)
- ✅ Projects module deployed
- ✅ Budgets module deployed
- ✅ Mail configuration complete

## Installation Steps

### 1. Database Migration

Run migrations to create necessary tables:

```bash
# Navigate to project root
cd /path/to/CANZIM-FinTrack

# Run migrations
php artisan migrate

# Expected output:
# - create expenses table
# - create expense_categories table
# - create expense_approvals table
```

**Verification:**

```bash
# Check tables exist
php artisan tinker
>>> DB::table('expenses')->count();
>>> DB::table('expense_categories')->count();
>>> DB::table('expense_approvals')->count();
```

### 2. Seed Expense Categories

Create default expense categories:

```bash
php artisan db:seed --class=ExpenseCategorySeeder
```

**Default Categories Created:**

- Office Supplies
- Travel & Transport
- Accommodation
- Training & Workshops
- Professional Services
- Communication
- Equipment Purchase
- Utilities
- Administrative Costs
- Other Expenses

**Verification:**

```bash
php artisan tinker
>>> App\Models\ExpenseCategory::all()->pluck('name');
```

### 3. Configure Storage

Ensure file storage is properly configured:

```bash
# Create storage link (if not exists)
php artisan storage:link

# Set permissions
chmod -R 775 storage/
chmod -R 775 public/storage/

# Create receipts directory
mkdir -p storage/app/public/receipts
chmod 775 storage/app/public/receipts
```

**Verification:**

```bash
# Check symbolic link
ls -la public/storage

# Should show: storage -> ../storage/app/public
```

### 4. Environment Configuration

Update `.env` file with necessary configurations:

```env
# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@can-zimbabwe.org
MAIL_FROM_NAME="${APP_NAME}"

# File Upload Configuration
FILESYSTEM_DISK=public
MAX_UPLOAD_SIZE=5120  # 5MB in kilobytes

# Application URL (for receipt links)
APP_URL=https://fintrack.can-zimbabwe.org
```

### 5. Install Frontend Dependencies

Build frontend assets:

```bash
# Install npm packages (if not already done)
npm install

# Build for production
npm run build

# Or for development
npm run dev
```

**Verification:**

```bash
# Check built assets exist
ls -la public/build/assets/
# Should contain app-*.js and app-*.css files
```

### 6. Clear Caches

Clear all application caches:

```bash
# Clear all caches
php artisan optimize:clear

# Individual cache clears
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 7. Run Tests

Verify everything works:

```bash
# Run expense tests
php artisan test --filter=ExpenseManagementTest

# Expected: 10/10 tests passing
```

## Post-Deployment Verification

### 1. Check Database Tables

```sql
-- Verify tables exist
SHOW TABLES LIKE 'expense%';

-- Check expenses table structure
DESCRIBE expenses;

-- Verify indexes
SHOW INDEX FROM expenses;

-- Sample data check
SELECT COUNT(*) FROM expense_categories;
```

### 2. Test File Uploads

```bash
# Create test expense with receipt
php artisan tinker

>>> $user = App\Models\User::where('email', 'project.officer@test.com')->first();
>>> $project = App\Models\Project::first();
>>> $budgetItem = $project->budgets()->first()->items()->first();
>>> $category = App\Models\ExpenseCategory::first();

>>> $expense = App\Models\Expense::create([
    'expense_number' => 'EXP-TEST-001',
    'project_id' => $project->id,
    'budget_item_id' => $budgetItem->id,
    'expense_category_id' => $category->id,
    'expense_date' => now(),
    'amount' => 100.00,
    'description' => 'Test expense',
    'submitted_by' => $user->id,
    'receipt_path' => 'receipts/2025/11/test.pdf'
]);

>>> \Storage::disk('public')->put('receipts/2025/11/test.pdf', 'test content');
>>> \Storage::disk('public')->exists($expense->receipt_path);
// Should return: true
```

### 3. Test API Endpoints

```bash
# Get authentication token first
curl -X POST https://your-domain.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "project.officer@test.com",
    "password": "password"
  }'

# Save the token from response

# Test list expenses
curl -X GET https://your-domain.com/api/v1/expenses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Should return: {"data": [...], "current_page": 1, ...}
```

### 4. Test Notifications

```bash
# Test email configuration
php artisan tinker

>>> Mail::raw('Test notification', function($message) {
    $message->to('test@example.com')
            ->subject('Test Email from FinTrack');
});

# Check if email received
```

### 5. Test Frontend Access

1. **Navigate to Expenses Page**
    - URL: `https://your-domain.com/expenses`
    - Should display expense list
    - Check for proper styling (Tailwind CSS)

2. **Test Create Expense**
    - Click "New Expense" button
    - Fill form
    - Upload test receipt
    - Submit

3. **Test Workflow**
    - Submit expense as Project Officer
    - Review as Finance Officer
    - Approve as Programs Manager
    - Mark as Paid as Finance Officer

## Web Server Configuration

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName fintrack.can-zimbabwe.org
    DocumentRoot /var/www/CANZIM-FinTrack/public

    <Directory /var/www/CANZIM-FinTrack/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Increase upload limits for receipts
    php_value upload_max_filesize 10M
    php_value post_max_size 10M

    ErrorLog ${APACHE_LOG_DIR}/fintrack-error.log
    CustomLog ${APACHE_LOG_DIR}/fintrack-access.log combined
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name fintrack.can-zimbabwe.org;
    root /var/www/CANZIM-FinTrack/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload limits
    client_max_body_size 10M;
}
```

## Performance Optimization

### 1. Enable Caching

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### 2. Enable OPcache

Add to `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 3. Queue Configuration (Optional)

For better performance with notifications:

```bash
# Set queue driver in .env
QUEUE_CONNECTION=database

# Run queue worker
php artisan queue:work --daemon

# Or use supervisor for production
```

**Supervisor Configuration** (`/etc/supervisor/conf.d/fintrack-queue.conf`):

```ini
[program:fintrack-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/CANZIM-FinTrack/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/CANZIM-FinTrack/storage/logs/queue.log
stopwaitsecs=3600
```

## Monitoring & Logging

### 1. Application Logging

Check logs regularly:

```bash
# View latest logs
tail -f storage/logs/laravel.log

# Check for errors
grep ERROR storage/logs/laravel.log
```

### 2. Database Monitoring

Monitor expense operations:

```sql
-- Check expense counts by status
SELECT status, COUNT(*) FROM expenses GROUP BY status;

-- Check recent expenses
SELECT expense_number, status, created_at
FROM expenses
ORDER BY created_at DESC
LIMIT 10;

-- Check approval activity
SELECT DATE(approved_at) as date, COUNT(*)
FROM expense_approvals
GROUP BY DATE(approved_at)
ORDER BY date DESC;
```

### 3. File Storage Monitoring

```bash
# Check storage usage
du -sh storage/app/public/receipts/

# Count receipt files
find storage/app/public/receipts/ -type f | wc -l

# Check for orphaned files (receipts not in database)
# Run custom command or script
```

## Backup Procedures

### 1. Database Backup

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p fintrack_db > /backups/fintrack_${DATE}.sql
gzip /backups/fintrack_${DATE}.sql

# Keep last 30 days
find /backups/ -name "fintrack_*.sql.gz" -mtime +30 -delete
```

### 2. File Storage Backup

```bash
# Backup receipts
tar -czf /backups/receipts_${DATE}.tar.gz storage/app/public/receipts/

# Sync to remote backup
rsync -avz storage/app/public/receipts/ user@backup-server:/backups/receipts/
```

## Rollback Procedures

### If Deployment Fails

```bash
# 1. Rollback migrations
php artisan migrate:rollback

# 2. Restore previous code version
git checkout previous-stable-tag

# 3. Rebuild frontend
npm run build

# 4. Clear caches
php artisan optimize:clear

# 5. Verify application works
php artisan test
```

### If Data Issues Occur

```bash
# 1. Stop application (maintenance mode)
php artisan down

# 2. Restore database backup
mysql -u root -p fintrack_db < /backups/fintrack_YYYYMMDD.sql

# 3. Restore receipt files if needed
tar -xzf /backups/receipts_YYYYMMDD.tar.gz -C storage/app/public/

# 4. Bring application back online
php artisan up
```

## Security Hardening

### 1. File Permissions

```bash
# Set correct ownership
chown -R www-data:www-data /var/www/CANZIM-FinTrack

# Set directory permissions
find /var/www/CANZIM-FinTrack -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/CANZIM-FinTrack -type f -exec chmod 644 {} \;

# Protect sensitive files
chmod 600 .env
chmod 755 artisan
```

### 2. Disable Debug Mode

In `.env`:

```env
APP_DEBUG=false
APP_ENV=production
```

### 3. Enable HTTPS

```bash
# Install certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d fintrack.can-zimbabwe.org
```

### 4. Configure CORS (if needed)

In `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['https://fintrack.can-zimbabwe.org'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Troubleshooting Deployment Issues

### Common Issues & Solutions

#### Issue: 500 Error on Expenses Page

**Solution:**

```bash
# Check permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Clear caches
php artisan optimize:clear

# Check logs
tail -f storage/logs/laravel.log
```

#### Issue: File Upload Fails

**Solution:**

```bash
# Check storage link
ls -la public/storage

# Recreate if missing
rm public/storage
php artisan storage:link

# Check permissions
chmod 775 storage/app/public/receipts/
```

#### Issue: Notifications Not Sending

**Solution:**

```bash
# Test mail configuration
php artisan tinker
>>> config('mail');

# Check queue
php artisan queue:work --once

# Verify SMTP credentials
```

#### Issue: Frontend Not Loading

**Solution:**

```bash
# Rebuild assets
npm run build

# Check for JS errors in browser console

# Verify vite manifest exists
ls -la public/build/manifest.json
```

## Support & Maintenance

### Regular Maintenance Tasks

**Daily:**

- Monitor error logs
- Check notification queue
- Verify backup completion

**Weekly:**

- Review expense approval metrics
- Check database performance
- Update documentation if needed

**Monthly:**

- Analyze storage usage
- Review security logs
- Performance optimization review
- Backup verification/testing

### Getting Help

**Technical Support:**

- Email: dev-team@can-zimbabwe.org
- Slack: #fintrack-support
- Documentation: https://docs.fintrack.can-zimbabwe.org

**Emergency Contacts:**

- System Administrator: +263-XXX-XXXX
- Database Admin: +263-XXX-XXXX
- On-Call Developer: +263-XXX-XXXX

---

**Deployment Version**: 1.0.0  
**Last Updated**: November 15, 2025  
**Deployment Lead**: DevOps Team, CAN-Zimbabwe
