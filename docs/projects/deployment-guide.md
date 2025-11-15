# Module 6: Deployment Guide

**Module**: Project & Budget Management  
**Version**: 1.0.0  
**Status**: Production Ready  
**Date**: November 15, 2025

---

## 🚀 Pre-Deployment Checklist

### Requirements Verification

- [x] PHP 8.2+ installed
- [x] Laravel 12 framework
- [x] MySQL/PostgreSQL database
- [x] Node.js 18+ & npm
- [x] Composer dependencies installed
- [x] All environment variables configured

### Quality Gates

- [x] All 18 tests passing (100%)
- [x] Build successful (zero errors)
- [x] Code formatted with Pint
- [x] Documentation complete
- [x] Zero regressions

---

## 📦 Deployment Steps

### Step 1: Database Setup

```bash
# Run migrations
php artisan migrate

# Seed budget categories
php artisan db:seed --class=BudgetCategorySeeder

# (Optional) Seed sample projects for testing
php artisan db:seed --class=ProjectSeeder
```

**Verification**:

```bash
# Verify tables exist
php artisan tinker
>>> DB::table('projects')->count();
>>> DB::table('budget_categories')->count();  // Should be 5
```

### Step 2: Build Frontend Assets

```bash
# Install dependencies (if not already done)
npm install

# Build for production
npm run build
```

**Expected Output**:

```
✓ built in ~2s
✓ app.js: 499.72 kB (gzip: 159.74 kB)
✓ app.css: 123.89 kB (gzip: 30.70 kB)
```

### Step 3: Run Tests

```bash
# Run all module tests
php artisan test --filter=ProjectManagementTest
```

**Expected Result**:

```
Tests:    18 passed (72 assertions)
Duration: ~4s
```

### Step 4: Configure Environment

Ensure these variables are set in `.env`:

```env
# Application
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# DomPDF for Reports
DOMPDF_ENABLE_REMOTE=true
DOMPDF_ENABLE_PHP=false

# Queue (for notifications)
QUEUE_CONNECTION=database  # or redis for production
```

### Step 5: Cache Configuration

```bash
# Cache routes
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 6: Set Permissions

```bash
# Storage permissions
chmod -R 775 storage bootstrap/cache

# Ensure web server can write
chown -R www-data:www-data storage bootstrap/cache
```

### Step 7: Start Queue Worker (Optional)

For budget notifications:

```bash
# Using supervisor (recommended for production)
php artisan queue:work --tries=3 --timeout=90
```

Or configure supervisor:

```ini
[program:fintrack-worker]
command=php /path/to/artisan queue:work --tries=3 --timeout=90
directory=/path/to/project
user=www-data
autostart=true
autorestart=true
```

---

## ✅ Post-Deployment Verification

### 1. Health Check Endpoints

```bash
# Test API endpoints
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/projects

curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/budgets/categories
```

### 2. UI Navigation Check

Visit these URLs and verify they load:

- `/projects` - Projects list page
- `/projects/create` - Create project form
- `/budgets` - Budgets list page
- `/budgets/create` - Create budget form

### 3. PDF Generation Test

```bash
# Via Tinker
php artisan tinker
>>> $project = App\Models\Project::first();
>>> $service = app(\App\Services\ReportService::class);
>>> $pdf = $service->generateProjectReport($project);
>>> echo "PDF generated successfully";
```

### 4. Notification Test

```bash
# Test budget approval notification
php artisan tinker
>>> $budget = App\Models\Budget::first();
>>> $user = App\Models\User::first();
>>> $user->notify(new \App\Notifications\BudgetApprovedNotification($budget, 'Test'));
>>> echo "Notification sent";
```

### 5. Permission Verification

Test with different user roles:

- Programs Manager: Full access ✓
- Finance Officer: View & approve budgets ✓
- Project Officer: View assigned projects only ✓

---

## 🔧 Troubleshooting

### Issue: PDF Generation Fails

**Solution**:

```bash
# Check DomPDF config
php artisan config:clear
php artisan config:cache

# Verify storage permissions
ls -la storage/app/
chmod -R 775 storage/
```

### Issue: Notifications Not Sending

**Solution**:

```bash
# Check queue table exists
php artisan queue:table
php artisan migrate

# Process queue manually
php artisan queue:work

# Check failed jobs
php artisan queue:failed
```

### Issue: 500 Error on Project Pages

**Solution**:

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue: Build Errors

**Solution**:

```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 📊 Monitoring

### Key Metrics to Monitor

1. **API Response Times**
    - Projects list: < 500ms
    - Budget list: < 500ms
    - PDF generation: < 3s

2. **Error Rates**
    - Target: < 1% error rate
    - Monitor 500 errors
    - Track failed jobs

3. **Database Performance**
    - Watch for N+1 queries
    - Monitor slow queries (> 1s)
    - Check connection pool

4. **Queue Processing**
    - Notification delivery time
    - Failed job count
    - Queue depth

### Logging

Important log events to monitor:

```php
// Budget approval
Log::info('Budget approved', ['budget_id' => $id]);

// Budget alert triggered
Log::warning('Budget threshold reached', [
    'budget_id' => $id,
    'utilization' => $percent
]);

// PDF generation
Log::info('PDF report generated', ['project_id' => $id]);

// Errors
Log::error('Budget creation failed', [
    'error' => $e->getMessage()
]);
```

---

## 🔒 Security Checklist

- [x] API authentication via Sanctum
- [x] CSRF protection enabled
- [x] SQL injection prevention (Eloquent ORM)
- [x] XSS prevention (Blade escaping)
- [x] Authorization via policies
- [x] Input validation on all forms
- [x] Rate limiting configured
- [x] HTTPS enforced (production)
- [x] Sensitive data encrypted
- [x] Error messages sanitized

---

## 📈 Performance Optimization

### Database Indexes

Ensure these indexes exist:

```sql
-- Projects
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_projects_created_at ON projects(created_at);

-- Budgets
CREATE INDEX idx_budgets_project_id ON budgets(project_id);
CREATE INDEX idx_budgets_status ON budgets(status);

-- Budget Items
CREATE INDEX idx_budget_items_budget_id ON budget_items(budget_id);
CREATE INDEX idx_budget_items_category_id ON budget_items(category_id);
```

### Eager Loading

Already implemented in services:

```php
// ProjectService
Project::with(['donors', 'budgets', 'team'])
    ->paginate(15);

// BudgetService
Budget::with(['project', 'items', 'donor'])
    ->get();
```

### Caching Strategy

```php
// Cache budget categories (rarely change)
Cache::remember('budget_categories', 3600, function () {
    return BudgetCategory::all();
});

// Cache project counts
Cache::remember('project_stats', 600, function () {
    return [
        'total' => Project::count(),
        'active' => Project::active()->count(),
    ];
});
```

---

## 🔄 Rollback Plan

If issues occur post-deployment:

### Step 1: Restore Previous Build

```bash
# Restore previous assets
git checkout HEAD~1 public/build/

# Rebuild if needed
npm run build
```

### Step 2: Database Rollback

```bash
# Check current batch
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback --step=1
```

### Step 3: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 4: Restart Services

```bash
# Restart queue workers
sudo supervisorctl restart fintrack-worker

# Restart web server
sudo systemctl restart nginx
# or
sudo systemctl restart apache2
```

---

## 📞 Support

### Module Information

- **Version**: 1.0.0
- **Deployed**: November 15, 2025
- **Tests**: 18/18 passing
- **Endpoints**: 15 API endpoints

### Documentation Links

- API Reference: `/docs/projects/api-reference.md`
- Module Overview: `/docs/projects/overview.md`
- Test Results: `/docs/projects/test-results.md`
- Module Status: `/docs/projects/module-status.md`

### Contact

- **Development Team**: [Your Team]
- **Documentation**: `/docs/projects/`
- **Issue Tracker**: [Your Issue Tracker]

---

## ✅ Deployment Verification Checklist

After deployment, verify:

- [ ] All 18 tests passing on production
- [ ] Database migrations completed
- [ ] Budget categories seeded (5 total)
- [ ] Frontend assets built successfully
- [ ] All 6 pages load correctly
- [ ] PDF generation working
- [ ] Notifications sending (if queue configured)
- [ ] API endpoints responding
- [ ] Authentication working
- [ ] Authorization policies enforced
- [ ] Sidebar navigation functional
- [ ] Search & filters working
- [ ] Dark mode functioning
- [ ] Mobile responsive
- [ ] No console errors
- [ ] No server errors in logs

---

**Deployment Status**: Ready ✅  
**Last Updated**: November 15, 2025  
**Module**: 6 - Project & Budget Management  
**Version**: 1.0.0
