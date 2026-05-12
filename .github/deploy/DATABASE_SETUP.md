# Database Creation Instructions

## Option 1: Via CyberPanel UI (Recommended - Easiest)

1. Log in to CyberPanel at `https://158.220.103.133:8090`
2. Go to **Databases → Create Database**
3. Enter:
    - **Database Name**: `canzim_fintrack`
    - **Database Username**: `canzim_user`
    - **Database Password**: `C#dVqI6Z5lel@AjHA1`
    - **Select Website**: blaxium.com
4. Click **Create Database**

## Option 2: Via MySQL Command Line (Quick)

SSH to your server as root and run:

```bash
mysql -u root << 'MYSQL_EOF'
CREATE DATABASE IF NOT EXISTS canzim_fintrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'canzim_user'@'localhost' IDENTIFIED BY 'C#dVqI6Z5lel@AjHA1';
GRANT ALL PRIVILEGES ON canzim_fintrack.* TO 'canzim_user'@'localhost';
FLUSH PRIVILEGES;
SHOW DATABASES LIKE 'canzim%';
MYSQL_EOF
```

## Option 3: Using CyberPanel CLI

```bash
cyberpanel createDatabase --databaseWebsite blaxium.com --dbName canzim_fintrack --dbUsername canzim_user --dbPassword 'C#dVqI6Z5lel@AjHA1'
```

## Verification

After creating the database, verify with:

```bash
mysql -u canzim_user -p'C#dVqI6Z5lel@AjHA1' -e "SHOW DATABASES;"
```

You should see `canzim_fintrack` in the list.

---

**After you create the database, let me know and I'll continue with the migrations.**
