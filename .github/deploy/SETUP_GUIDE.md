# CANZIM-FinTrack Deployment Setup Guide

## 🚀 Quick Setup Steps

Follow these steps to configure and deploy your application to production.

---

## Step 1: Generate SSH Keys for Deployment

On your **local machine**, generate an SSH key pair for GitHub Actions:

```bash
# Generate a new SSH key
ssh-keygen -t ed25519 -C "github-actions-canzim" -f ~/.ssh/canzim-deploy

# This creates two files:
# - ~/.ssh/canzim-deploy (private key - for GitHub Secrets)
# - ~/.ssh/canzim-deploy.pub (public key - for server)
```

## Step 2: Add Public Key to Server

Copy the public key to your production server:

```bash
# View the public key
cat ~/.ssh/canzim-deploy.pub

# Copy the output, then SSH to your server
ssh blaxi2540@158.220.103.133

# On the server, add the public key to authorized_keys
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
# Paste the public key on a new line
# Save and exit (Ctrl+X, Y, Enter)
chmod 600 ~/.ssh/authorized_keys
exit
```

Test the connection:
```bash
ssh -i ~/.ssh/canzim-deploy blaxi2540@158.220.103.133
```

## Step 3: Configure GitHub Secrets

1. Go to your GitHub repository: https://github.com/bguvava/CANZIM-FinTrack

2. Navigate to: **Settings** → **Secrets and variables** → **Actions**

3. Click **"New repository secret"** and add these secrets:

### Required Secrets:

#### SSH_PRIVATE_KEY
```bash
# Get the private key content
cat ~/.ssh/canzim-deploy
# Copy the ENTIRE output including -----BEGIN and -----END lines
```
- Value: Paste the entire private key

#### SSH_USER
- Value: `blaxi2540`

#### SERVER_IP
- Value: `158.220.103.133`

#### DB_PASSWORD
- Value: Your production database password (create a strong one)
- Example: `C@nz1m#Prod2026!Secure`

#### APP_KEY (Generate on server later)
- Value: Will be generated - leave empty for now

## Step 4: Initial Server Setup

SSH to your server and run the initial setup:

```bash
# Connect to server
ssh blaxi2540@158.220.103.133

# Download and run the setup script
cd /tmp
wget https://raw.githubusercontent.com/bguvava/CANZIM-FinTrack/main/.github/scripts/initial-server-setup.sh
chmod +x initial-server-setup.sh
bash initial-server-setup.sh
```

The script will:
- Create application directory
- Clone repository
- Install dependencies
- Setup .env file
- Generate APP_KEY
- Prompt for database setup

## Step 5: Configure Production Environment

Edit the `.env` file on the server:

```bash
nano /home/blaxium.com/canzim.blaxium.com/.env
```

Update these critical values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://canzim.blaxium.com

DB_CONNECTION=mysql
DB_DATABASE=canzim_fintrack
DB_USERNAME=canzim_user
DB_PASSWORD=your_secure_password_here

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
```

## Step 6: Setup Database

On the server, create the production database:

```bash
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE canzim_fintrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'canzim_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON canzim_fintrack.* TO 'canzim_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Run migrations:
```bash
cd /home/blaxium.com/canzim.blaxium.com
php artisan migrate --force
```

## Step 7: Commit and Push CI/CD Configuration

On your **local machine**:

```bash
cd c:\xampp\htdocs\CANZIM-FinTrack

# Add the CI/CD files
git add .github/
git add .gitignore
git add package.json

# Commit the changes
git commit -m "ci: add GitHub Actions CI/CD pipeline

- Add CI workflow for code quality, tests, and security
- Add CD workflow for production deployment
- Add staging workflow for develop branch
- Add deployment scripts and documentation
- Configure deployment checklist and guides"

# Push to GitHub
git push origin main
```

## Step 8: Test CI Pipeline

The CI pipeline should run automatically on push. Check:

1. Go to: https://github.com/bguvava/CANZIM-FinTrack/actions
2. You should see "CI - Continuous Integration" workflow running
3. Wait for it to complete
4. All checks should pass ✅

If any checks fail:
- Review the error logs in GitHub Actions
- Fix issues locally
- Push fixes and repeat

## Step 9: Deploy to Production (First Time)

### Option A: Manual Deployment (Recommended for first time)

1. Go to: https://github.com/bguvava/CANZIM-FinTrack/actions
2. Click on "CD - Deploy to Production" workflow
3. Click **"Run workflow"** dropdown
4. Select branch: `main`
5. Type: **`DEPLOY`** in the confirmation field
6. Click **"Run workflow"**
7. Monitor the deployment progress

### Option B: Via Git Tag

```bash
# On local machine
git tag v1.0.0
git push origin v1.0.0
```

## Step 10: Verify Production Deployment

After deployment completes:

1. **Check Application**
   - Visit: https://canzim.blaxium.com
   - Should see the login page

2. **Check Health Endpoint**
   ```bash
   curl https://canzim.blaxium.com/api/v1/health
   # Should return: {"status":"success",...}
   ```

3. **Check Logs on Server**
   ```bash
   ssh blaxi2540@158.220.103.133
   tail -f /home/blaxium.com/canzim.blaxium.com/storage/logs/laravel.log
   ```

4. **Test Login**
   - Try logging in with admin credentials
   - Verify dashboard loads
   - Check key features work

---

## Ongoing Development Workflow

### For Feature Development:

```bash
# 1. Create feature branch
git checkout -b feature/your-feature-name

# 2. Make changes and commit
git add .
git commit -m "feat: add new feature"

# 3. Push to GitHub
git push origin feature/your-feature-name

# 4. Create Pull Request on GitHub
# - CI runs automatically
# - Wait for checks to pass
# - Get code review
# - Merge to main

# 5. Deploy to production (manual trigger or tag)
```

### For Hotfixes:

```bash
# 1. Create hotfix branch
git checkout -b hotfix/critical-bug

# 2. Fix and commit
git add .
git commit -m "fix: resolve critical bug"

# 3. Push and deploy ASAP
git push origin hotfix/critical-bug
# Create PR, merge, deploy
```

---

## Rollback Procedure

If something goes wrong after deployment:

### Automatic Rollback
- If deployment fails, workflow automatically rolls back
- Check GitHub Actions logs for details

### Manual Rollback
```bash
ssh blaxi2540@158.220.103.133
cd /home/blaxium.com
rsync -av --delete canzim.blaxium.com.backup/ canzim.blaxium.com/
cd canzim.blaxium.com
php artisan migrate:rollback --step=1
php artisan optimize
php artisan up
```

---

## Monitoring and Maintenance

### Daily Checks:
- Monitor error logs
- Check application uptime
- Review GitHub Actions for failed builds

### Weekly Tasks:
- Review deployment logs
- Check for security updates
- Test backup restoration

### Monthly Tasks:
- Update dependencies
- Review and optimize database
- Audit security configurations

---

## Troubleshooting

### Issue: SSH Connection Fails
**Solution:**
```bash
# Test SSH connection
ssh -i ~/.ssh/canzim-deploy -v blaxi2540@158.220.103.133

# Check server SSH config
# Ensure public key is in authorized_keys
```

### Issue: GitHub Actions Can't Connect to Server
**Solution:**
- Verify SSH_PRIVATE_KEY secret is set correctly
- Check SERVER_IP is correct
- Ensure server firewall allows GitHub IPs

### Issue: Deployment Script Fails
**Solution:**
```bash
# SSH to server and check logs
ssh blaxi2540@158.220.103.133
tail -f /home/blaxium.com/canzim.blaxium.com/storage/logs/laravel.log
```

### Issue: Application Shows 500 Error
**Solution:**
```bash
# Check Laravel logs
ssh blaxi2540@158.220.103.133
cd /home/blaxium.com/canzim.blaxium.com

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
```

---

## Support Resources

- **Deployment Guide:** `.github/deploy/DEPLOYMENT_GUIDE.md`
- **Checklist:** `.github/deploy/DEPLOYMENT_CHECKLIST.md`
- **GitHub Actions:** https://github.com/bguvava/CANZIM-FinTrack/actions
- **Server Info:** `.github/deploy/info.txt`

---

## Security Reminders

✅ **DO:**
- Use strong passwords
- Keep secrets secure
- Monitor logs regularly
- Update dependencies
- Test before deploying

❌ **DON'T:**
- Commit `.env` files
- Use DEBUG=true in production
- Share SSH private keys
- Skip testing
- Deploy without backups

---

**Last Updated:** April 9, 2026  
**Version:** 1.0.0
