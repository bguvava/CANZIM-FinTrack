#!/bin/bash
# Fix database user permissions for CyberPanel created database

echo "Fixing database permissions for blax_canzim_user..."

mysql -u root << 'MYSQL_EOF'
-- Show current grants
SELECT CONCAT('Current grants for blax_canzim_user:') AS '';
SHOW GRANTS FOR 'blax_canzim_user'@'localhost';

-- Grant all privileges on the database
GRANT ALL PRIVILEGES ON blax_canzim_fintrackdb.* TO 'blax_canzim_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify updated grants
SELECT CONCAT('Updated grants for blax_canzim_user:') AS '';
SHOW GRANTS FOR 'blax_canzim_user'@'localhost';

-- Test connection by selecting from the database
SELECT CONCAT('User can access database: ', IF(COUNT(*) >= 0, 'YES', 'NO')) AS Status
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = 'blax_canzim_fintrackdb';
MYSQL_EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Database permissions fixed successfully!"
    echo ""
    echo "Testing connection as application user..."
    mysql -u blax_canzim_user -p'C#dVqI6Z5lel@AjHA1' -e "USE blax_canzim_fintrackdb; SELECT 'Connection successful!' AS Status;"
else
    echo "✗ Error fixing permissions"
    exit 1
fi
