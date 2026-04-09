# CI/CD Pipeline

This directory contains the Continuous Integration and Continuous Deployment (CI/CD) configuration for CANZIM-FinTrack.

## Directory Structure

```
.github/
├── workflows/              # GitHub Actions workflow definitions
│   ├── ci.yml             # Continuous Integration (tests, linting, security)
│   ├── cd-production.yml  # Production deployment workflow
│   └── staging.yml        # Staging deployment workflow
├── scripts/               # Deployment and automation scripts
│   ├── deploy-production.sh      # Production deployment script
│   └── initial-server-setup.sh   # First-time server setup script
└── deploy/                # Deployment documentation and templates
    ├── DEPLOYMENT_GUIDE.md       # Comprehensive deployment guide
    ├── DEPLOYMENT_CHECKLIST.md   # Pre/post-deployment checklist
    ├── .env.production.template  # Production environment template
    ├── info.txt                  # Server information (not tracked)
    └── vHost Conf                # Virtual host configuration (not tracked)
```

## Quick Start

### For Developers

1. **Make Changes**
   - Create feature branch
   - Write code and tests
   - Commit changes

2. **Push to GitHub**
   ```bash
   git push origin feature/your-feature
   ```

3. **CI Runs Automatically**
   - Code quality checks
   - Tests execution
   - Security scanning

4. **Create Pull Request**
   - Merge to `develop` for staging
   - Merge to `main` for production release

### For Deployment

#### First-Time Setup

1. **Configure GitHub Secrets**
   - Go to: Repository → Settings → Secrets and variables → Actions
   - Add required secrets (see [DEPLOYMENT_GUIDE.md](deploy/DEPLOYMENT_GUIDE.md))

2. **Run Initial Server Setup**
   ```bash
   ssh user@server
   bash < initial-server-setup.sh
   ```

3. **Configure Production Environment**
   - Edit `.env` on server
   - Use `.env.production.template` as reference

#### Deploy to Production

**Option 1: Manual Trigger (Recommended)**
1. Go to GitHub → Actions → "CD - Deploy to Production"
2. Click "Run workflow"
3. Type "DEPLOY" in the confirmation field
4. Click "Run workflow"

**Option 2: Tag-Based (Automated)**
```bash
git tag v1.0.0
git push origin v1.0.0
```

## Workflows

### CI Workflow (`ci.yml`)
**Triggers:** Every push, every pull request

**What it does:**
- ✅ Checks PHP code style (Laravel Pint)
- ✅ Checks JavaScript/Vue code style (ESLint)
- ✅ Runs all tests with coverage
- ✅ Scans for security vulnerabilities
- ✅ Builds frontend assets

**Required to pass before deployment**

### Staging Workflow (`staging.yml`)
**Triggers:** Push to `develop` branch

**What it does:**
- Builds application
- Runs tests
- Prepares for staging deployment

### Production Workflow (`cd-production.yml`)
**Triggers:** Manual or version tags

**What it does:**
- ✅ Runs pre-deployment verification
- ✅ Creates backup of current version
- ✅ Deploys new version
- ✅ Runs database migrations
- ✅ Optimizes application
- ✅ Verifies deployment success
- 🔄 Auto-rollback on failure

## Required GitHub Secrets

| Secret | Description | How to Get |
|--------|-------------|------------|
| `SSH_PRIVATE_KEY` | Private SSH key for server access | `cat ~/.ssh/id_rsa` |
| `SSH_USER` | SSH username | Server user (e.g., blaxi2540) |
| `SERVER_IP` | Production server IP | From hosting provider |
| `DB_PASSWORD` | Production database password | Create secure password |
| `APP_KEY` | Laravel app key | Run `php artisan key:generate` |

## Documentation

- **[DEPLOYMENT_GUIDE.md](deploy/DEPLOYMENT_GUIDE.md)** - Complete deployment documentation
- **[DEPLOYMENT_CHECKLIST.md](deploy/DEPLOYMENT_CHECKLIST.md)** - Pre/post-deployment checklist
- **[.env.production.template](deploy/.env.production.template)** - Production environment template

## Scripts

### `deploy-production.sh`
Production deployment script that handles:
- Backup creation
- Code deployment
- Dependency installation
- Database migrations
- Cache optimization
- Service restart
- Verification checks

### `initial-server-setup.sh`
First-time server setup script for:
- Cloning repository
- Installing dependencies
- Configuring environment
- Setting permissions
- Initial database setup

## Monitoring

### Check Deployment Status
1. Go to GitHub → Actions
2. View workflow run details
3. Check logs for any errors

### Application Health
```bash
# SSH to server
ssh user@158.220.103.133

# Check Laravel logs
tail -f /home/blaxium.com/canzim.blaxium.com/storage/logs/laravel.log

# Check application status
cd /home/blaxium.com/canzim.blaxium.com
php artisan --version
php artisan migrate:status
```

## Rollback

### Automatic Rollback
If deployment fails, the workflow automatically:
1. Detects the failure
2. Restores from backup
3. Rolls back migrations
4. Brings application back online

### Manual Rollback
```bash
ssh user@server
cd /home/blaxium.com
rsync -av --delete canzim.blaxium.com.backup/ canzim.blaxium.com/
cd canzim.blaxium.com
php artisan migrate:rollback --step=1
php artisan optimize
php artisan up
```

## Troubleshooting

### Deployment Fails
1. Check GitHub Actions logs
2. Review server logs
3. Verify SSH access
4. Check disk space
5. Verify database connection

### Tests Fail in CI
1. Run tests locally: `php artisan test`
2. Check test logs in GitHub Actions
3. Fix failing tests
4. Push fix

### Assets Not Loading
1. Verify build completed: Check CI logs
2. Clear browser cache
3. Check file permissions on server
4. Rebuild: `npm run build`

## Best Practices

1. ✅ **Always** run tests locally before pushing
2. ✅ **Use** feature branches for development
3. ✅ **Review** pull requests before merging
4. ✅ **Tag** releases with semantic versioning
5. ✅ **Monitor** application after deployment
6. ✅ **Backup** before major changes
7. ❌ **Never** commit secrets or `.env` files
8. ❌ **Never** deploy with failing tests
9. ❌ **Never** skip code review

## Support

For issues or questions:
1. Check [DEPLOYMENT_GUIDE.md](deploy/DEPLOYMENT_GUIDE.md)
2. Review GitHub Actions logs
3. Check server logs
4. Contact system administrator

---

**Last Updated:** April 9, 2026  
**Maintained By:** Development Team
