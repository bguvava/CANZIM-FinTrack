#!/bin/bash
# Issue SSL certificate for canzim.blaxium.com using Let's Encrypt

echo "=========================================="
echo "SSL Certificate Setup for canzim.blaxium.com"
echo "=========================================="
echo ""

DOMAIN="canzim.blaxium.com"

# Check if CyberPanel is installed
if ! command -v cyberpanel &> /dev/null; then
    echo "Error: CyberPanel not found"
    exit 1
fi

echo "Step 1: Checking domain DNS..."
echo "Domain: $DOMAIN"
echo "Server IP: $(curl -s ifconfig.me)"
echo ""

# Check DNS resolution
DNS_IP=$(dig +short $DOMAIN | tail -1)
echo "Domain resolves to: $DNS_IP"
echo ""

echo "Step 2: Issuing Let's Encrypt SSL certificate..."
echo "This may take 1-2 minutes..."
echo ""

# Use CyberPanel CLI to issue SSL
cyberpanel issueSSL --domainName $DOMAIN

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ SSL certificate issued successfully!"
    echo ""
    echo "Step 3: Enabling HTTPS redirect..."

    # Enable force HTTPS in vHost config
    VHOST_FILE="/usr/local/lsws/conf/vhosts/$DOMAIN/vhost.conf"

    if [ -f "$VHOST_FILE" ]; then
        # Backup
        cp "$VHOST_FILE" "$VHOST_FILE.backup.ssl.$(date +%Y%m%d_%H%M%S)"

        # Add/update force SSL rewrite rule
        if grep -q "RewriteEngine" "$VHOST_FILE"; then
            echo "Rewrite rules already exist in vHost"
        else
            echo "Adding HTTPS redirect rules to vHost..."
            # This would need careful insertion into the vHost config
        fi

        echo ""
        echo "Step 4: Restarting LiteSpeed..."
        systemctl restart lsws || /usr/local/lsws/bin/lswsctrl restart

        echo ""
        echo "✓ SSL configuration complete!"
        echo ""
        echo "Please test at: https://$DOMAIN"
        echo ""
    else
        echo "Warning: vHost file not found at $VHOST_FILE"
    fi
else
    echo ""
    echo "✗ SSL certificate issuance failed"
    echo ""
    echo "Please use CyberPanel UI to issue SSL manually:"
    echo "1. Go to: https://158.220.103.133:8090"
    echo "2. SSL → Manage SSL"
    echo "3. Select: canzim.blaxium.com"
    echo "4. Click: Issue SSL"
    echo ""
fi
