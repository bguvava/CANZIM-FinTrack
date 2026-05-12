# Quick Server Setup Commands

## Step 1: Add SSH Key to Server

Open a NEW PowerShell window and run:

```powershell
ssh root@158.220.103.133
```

**Enter your root password when prompted**

Once connected to the server, run these commands:

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAID1XrwstGK818AoQyyRttrXUumCbdVqI6Z5lelaAjHA1 github-actions-canzim' >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
echo "SSH key added successfully!"
exit
```

## Step 2: Test SSH Connection (without password)

Back in your local PowerShell:

```powershell
ssh -i $env:USERPROFILE\.ssh\canzim-deploy root@158.220.103.133
```

This should connect WITHOUT asking for a password. Type `exit` when done.

## Step 3: Check GitHub Actions

Go to: https://github.com/bguvava/CANZIM-FinTrack/actions

You should see the CI workflow running automatically!

## Step 4: Run Initial Server Setup

Once SSH works (Step 2), download and run the setup script:

```powershell
$setupScript = @"
cd /home/blaxium.com
if [ -d "canzim.blaxium.com" ]; then
    echo "Directory exists, creating backup..."
    mv canzim.blaxium.com canzim.blaxium.com.backup.`$(date +%Y%m%d_%H%M%S)
fi
git clone https://github.com/bguvava/CANZIM-FinTrack.git canzim.blaxium.com
cd canzim.blaxium.com
composer install --no-dev --optimize-autoloader --no-interaction
cp .env.example .env
php artisan key:generate --force
echo "Setup complete! Now edit .env file with production settings."
"@

ssh -i $env:USERPROFILE\.ssh\canzim-deploy root@158.220.103.133 $setupScript
```

## Step 5: Configure Production Environment

SSH to the server and edit `.env`:

```powershell
ssh -i $env:USERPROFILE\.ssh\canzim-deploy root@158.220.103.133
```

Then on the server:

```bash
cd /home/blaxium.com/canzim.blaxium.com
nano .env
```

Update these values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://canzim.blaxium.com

DB_CONNECTION=mysql
DB_DATABASE=canzim_fintrack
DB_USERNAME=canzim_user
DB_PASSWORD=C#dVqI6Z5lel@AjHA1

MAIL_MAILER=smtp
# ... add your mail settings
```

Save with: `Ctrl+X`, then `Y`, then `Enter`

## Step 6: Create Database

Still on the server:

```bash
mysql -u root -p
```

Enter MySQL root password, then:

```sql
CREATE DATABASE canzim_fintrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'canzim_user'@'localhost' IDENTIFIED BY 'C#dVqI6Z5lel@AjHA1';
GRANT ALL PRIVILEGES ON canzim_fintrack.* TO 'canzim_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Run migrations:

```bash
cd /home/blaxium.com/canzim.blaxium.com
php artisan migrate --force
```

## Step 7: Build Frontend Assets

On your LOCAL machine:

```powershell
cd c:\xampp\htdocs\CANZIM-FinTrack
npm run build
```

Upload build to server:

```powershell
scp -i $env:USERPROFILE\.ssh\canzim-deploy -r public/build root@158.220.103.133:/home/blaxium.com/canzim.blaxium.com/public/
```

## Step 8: Set Permissions

On the server:

```bash
cd /home/blaxium.com/canzim.blaxium.com
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan optimize
```

## Step 9: Test the Application

Visit: https://canzim.blaxium.com

You should see your application!

## Step 10: Deploy Using GitHub Actions

Go to: https://github.com/bguvava/CANZIM-FinTrack/actions

1. Click "CD - Deploy to Production"
2. Click "Run workflow"
3. Type: **DEPLOY**
4. Click "Run workflow"

Monitor the deployment in real-time!

---

**If you encounter any issues, refer to:**

- Full Guide: `.github/deploy/DEPLOYMENT_GUIDE.md`
- Checklist: `.github/deploy/DEPLOYMENT_CHECKLIST.md`
- Setup Guide: `.github/deploy/SETUP_GUIDE.md`
