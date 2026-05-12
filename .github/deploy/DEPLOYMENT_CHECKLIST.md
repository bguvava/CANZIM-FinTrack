# Production Deployment Checklist

## Pre-Deployment

### Local Preparation

- [ ] All features tested locally
- [ ] All tests pass: `php artisan test`
- [ ] Code style check passes: `vendor/bin/pint --test`
- [ ] Frontend builds successfully: `npm run build`
- [ ] No debug code or console.log statements
- [ ] Version number updated in appropriate files
- [ ] CHANGELOG.md updated with changes
- [ ] Git working directory is clean

### Code Review

- [ ] Pull request created and reviewed
- [ ] All review comments addressed
- [ ] CI pipeline passes on GitHub Actions
- [ ] Security scan shows no vulnerabilities
- [ ] Code coverage meets threshold (80%+)

### Database

- [ ] New migrations reviewed and tested
- [ ] Migration rollback tested locally
- [ ] No data loss migrations (backup required if yes)
- [ ] Seeders updated if needed
- [ ] Database backup scheduled before deployment

## GitHub Configuration

### Secrets Verification

- [ ] SSH_PRIVATE_KEY configured
- [ ] SSH_USER configured (blaxi2540)
- [ ] SERVER_IP configured (158.220.103.133)
- [ ] DB_PASSWORD available for production .env
- [ ] APP_KEY available for production .env
- [ ] MAIL credentials available (if using email)

### Workflow Files

- [ ] `.github/workflows/ci.yml` present
- [ ] `.github/workflows/cd-production.yml` present
- [ ] `.github/workflows/staging.yml` present
- [ ] `.github/scripts/deploy-production.sh` present and executable

## Server Preparation

### Server Access

- [ ] SSH access to server verified
- [ ] Deployment user has correct permissions
- [ ] Application directory exists: `/home/blaxium.com/canzim.blaxium.com`
- [ ] Web server configuration verified (vHost Conf)

### Environment Setup

- [ ] Production `.env` file configured
- [ ] Database created and accessible
- [ ] Database user has all required privileges
- [ ] Storage directories writable
- [ ] SSL certificate valid for https://canzim.blaxium.com

### Dependencies

- [ ] PHP 8.2+ installed with required extensions
- [ ] Composer 2.x installed
- [ ] Node.js 20+ installed (if building on server)
- [ ] MySQL/MariaDB running
- [ ] Git installed

### Services

- [ ] Web server running (LiteSpeed)
- [ ] Database server running
- [ ] Queue worker configured (if using queues)
- [ ] Cron jobs configured (if needed)
- [ ] Supervisor configured (for queue workers)

## First Deployment

### Initial Setup (First Time Only)

- [ ] Repository cloned to server
- [ ] `.env` file created from template
- [ ] `APP_KEY` generated: `php artisan key:generate`
- [ ] Database seeded (if needed)
- [ ] Storage linked: `php artisan storage:link`
- [ ] Initial permissions set

### Testing

- [ ] Application accessible at domain
- [ ] Home page loads correctly
- [ ] Login functionality works
- [ ] Database connection successful
- [ ] File uploads work (if applicable)
- [ ] Email sending works (if applicable)

## Deployment Execution

### Pre-Deployment Actions

- [ ] Notify team of deployment window
- [ ] Current version documented
- [ ] Backup of current production taken
- [ ] Database backup completed
- [ ] Maintenance window scheduled (if needed)

### Deployment Process

- [ ] Trigger deployment workflow on GitHub Actions
- [ ] Type "DEPLOY" confirmation
- [ ] Monitor deployment progress in real-time
- [ ] Watch for errors in workflow logs

### Deployment Steps (Automated)

- [ ] Maintenance mode enabled
- [ ] Backup created
- [ ] New code deployed
- [ ] Composer dependencies installed
- [ ] Database migrations run
- [ ] Caches cleared and optimized
- [ ] Permissions set
- [ ] Services restarted
- [ ] Maintenance mode disabled

## Post-Deployment

### Verification

- [ ] Application loads at https://canzim.blaxium.com
- [ ] No 500/404 errors
- [ ] Health check endpoint responds
- [ ] Database migrations applied: `php artisan migrate:status`
- [ ] Key features tested manually
- [ ] User authentication works
- [ ] API endpoints respond correctly (if applicable)

### Performance

- [ ] Page load time acceptable
- [ ] No JavaScript errors in console
- [ ] Images and assets loading
- [ ] Database queries optimized
- [ ] Caches working correctly

### Monitoring

- [ ] Error logs checked: `storage/logs/laravel.log`
- [ ] Web server logs checked
- [ ] No spike in error rate
- [ ] Server resources normal (CPU, memory, disk)
- [ ] Application metrics stable

### Communication

- [ ] Team notified of successful deployment
- [ ] Release notes published (if applicable)
- [ ] Stakeholders informed
- [ ] Documentation updated

## Rollback Plan

### When to Rollback

- [ ] Critical bug discovered
- [ ] Performance degradation
- [ ] Data integrity issues
- [ ] Health checks failing
- [ ] User-reported critical issues

### Rollback Procedure

- [ ] Automatic rollback triggered (if deployment failed)
- [ ] Manual rollback steps documented
- [ ] Backup restoration tested
- [ ] Migration rollback executed
- [ ] Previous version verified working
- [ ] Team notified of rollback

## Ongoing Monitoring

### First Hour

- [ ] Monitor error logs continuously
- [ ] Watch user activity
- [ ] Check for unusual patterns
- [ ] Verify all features working

### First 24 Hours

- [ ] Review error reports
- [ ] Monitor performance metrics
- [ ] Check user feedback
- [ ] Verify scheduled tasks running

### First Week

- [ ] Analyze usage patterns
- [ ] Review security logs
- [ ] Check for memory leaks
- [ ] Verify backup schedules running

## Documentation

### Updated Documentation

- [ ] Deployment guide updated
- [ ] API documentation current (if applicable)
- [ ] User documentation updated
- [ ] Internal wiki updated
- [ ] Runbooks updated

### Version Control

- [ ] Release tag created: `git tag v1.x.x`
- [ ] Tag pushed to GitHub
- [ ] Release notes on GitHub Releases
- [ ] CHANGELOG.md updated

## Security

### Security Checks

- [ ] `.env` file not publicly accessible
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Error reporting appropriate for production
- [ ] Sensitive data not logged
- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] CORS configured correctly
- [ ] Rate limiting enabled

### Credentials

- [ ] No hardcoded credentials in code
- [ ] Database passwords strong and unique
- [ ] API keys rotated if needed
- [ ] SSH keys properly managed
- [ ] File permissions secure (755/644)

## Cleanup

### Post-Deployment Cleanup

- [ ] Old backups archived or deleted
- [ ] Temporary files removed
- [ ] Deployment logs reviewed
- [ ] Unused branches deleted
- [ ] Development dependencies not on production

## Sign-Off

**Deployment Date:** ******\_\_\_******

**Deployed By:** ******\_\_\_******

**Version Deployed:** ******\_\_\_******

**Deployment Status:** ✅ Success / ❌ Failed / ⏮️ Rolled Back

**Notes:**

```
_______________________________________________________________
_______________________________________________________________
_______________________________________________________________
```

**Verified By:** ******\_\_\_******

**Sign-Off:** ******\_\_\_******

---

## Emergency Contacts

**System Administrator:** ******\_\_\_******

**Development Team Lead:** ******\_\_\_******

**Database Administrator:** ******\_\_\_******

**On-Call Support:** ******\_\_\_******

---

**Template Version:** 1.0.0  
**Last Updated:** April 9, 2026
