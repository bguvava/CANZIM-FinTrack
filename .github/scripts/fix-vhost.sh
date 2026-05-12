#!/bin/bash
# Fix CyberPanel vHost configuration for Laravel

echo "Fixing vHost configuration for canzim.blaxium.com..."

# Find the vHost configuration file
VHOST_FILE="/usr/local/lsws/conf/vhosts/canzim.blaxium.com/vhost.conf"

if [ ! -f "$VHOST_FILE" ]; then
    echo "Error: vHost configuration file not found at $VHOST_FILE"
    echo "Searching for vHost config files..."
    find /usr/local/lsws/conf/vhosts/ -name "vhost.conf" -type f 2>/dev/null
    exit 1
fi

echo "Found vHost config: $VHOST_FILE"

# Backup the original configuration
cp "$VHOST_FILE" "$VHOST_FILE.backup.$(date +%Y%m%d_%H%M%S)"
echo "✓ Backup created"

# Update document root to point to public directory
sed -i 's|docRoot.*$VHROOT.*$|docRoot                  $VH_ROOT/public|g' "$VHOST_FILE"

echo "✓ Updated document root to public directory"

# Show the changes
echo ""
echo "Current vHost configuration (docRoot line):"
grep -A 2 "docRoot" "$VHOST_FILE"

echo ""
echo "✓ Configuration updated!"
echo ""
echo "Now restarting LiteSpeed web server..."

# Restart LiteSpeed
systemctl restart lsws || /usr/local/lsws/bin/lswsctrl restart

if [ $? -eq 0 ]; then
    echo "✓ LiteSpeed restarted successfully!"
    echo ""
    echo "Please test the application at: https://canzim.blaxium.com"
else
    echo "✗ Failed to restart LiteSpeed. Please restart manually via CyberPanel."
fi
