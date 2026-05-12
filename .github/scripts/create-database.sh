#!/bin/bash
# Create MySQL database and user for CANZIM FinTrack

DB_NAME="canzim_fintrack"
DB_USER="canzim_user"
DB_PASS='C#dVqI6Z5lel@AjHA1'

echo "Creating MySQL database and user..."

# Create database and user
mysql -u root <<MYSQL_SCRIPT
-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if it doesn't exist (MySQL 8.0 syntax)
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';

-- Grant all privileges on the database to the user
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SELECT User, Host FROM mysql.user WHERE User = '$DB_USER';
SHOW DATABASES LIKE '$DB_NAME';
MYSQL_SCRIPT

if [ $? -eq 0 ]; then
    echo "✓ Database '$DB_NAME' and user '$DB_USER' created successfully"
else
    echo "✗ Error creating database or user"
    exit 1
fi
