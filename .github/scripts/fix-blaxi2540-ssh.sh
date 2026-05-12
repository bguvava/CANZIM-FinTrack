#!/bin/bash
# Run this script AS ROOT to fix blaxi2540 SSH key authentication
# Usage: bash fix-blaxi2540-ssh.sh

echo "================================================"
echo "Fixing SSH Key Authentication for blaxi2540"
echo "================================================"

# Check if user exists
if ! id "blaxi2540" &>/dev/null; then
    echo "❌ User blaxi2540 does not exist!"
    exit 1
fi
echo "✓ User blaxi2540 exists"

# Remove and recreate .ssh directory
echo "Setting up .ssh directory..."
rm -rf /home/blaxi2540/.ssh
mkdir -p /home/blaxi2540/.ssh
chmod 700 /home/blaxi2540/.ssh

# Add SSH public key
echo "Adding GitHub Actions SSH public key..."
cat > /home/blaxi2540/.ssh/authorized_keys << 'EOF'
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAID1XrwstGK818AoQyyRttrXUumCbdVqI6Z5lelaAjHA1 github-actions-canzim
EOF

# Set correct permissions and ownership
chmod 600 /home/blaxi2540/.ssh/authorized_keys
chown -R blaxi2540:blaxi2540 /home/blaxi2540/.ssh
echo "✓ SSH key added and permissions set"

# Verify setup
echo ""
echo "Verifying setup:"
ls -la /home/blaxi2540/.ssh/
echo ""
echo "Authorized keys content:"
cat /home/blaxi2540/.ssh/authorized_keys
echo ""

# Check SSH configuration
echo "Checking SSH server configuration..."
if grep -q "^PubkeyAuthentication yes" /etc/ssh/sshd_config; then
    echo "✓ PubkeyAuthentication is enabled"
elif grep -q "^#PubkeyAuthentication yes" /etc/ssh/sshd_config; then
    echo "⚠ PubkeyAuthentication is commented out, enabling..."
    sed -i 's/^#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config
    systemctl restart sshd
    echo "✓ SSH service restarted"
elif grep -q "^PubkeyAuthentication no" /etc/ssh/sshd_config; then
    echo "⚠ PubkeyAuthentication is disabled, enabling..."
    sed -i 's/^PubkeyAuthentication no/PubkeyAuthentication yes/' /etc/ssh/sshd_config
    systemctl restart sshd
    echo "✓ SSH service restarted"
else
    echo "⚠ PubkeyAuthentication not explicitly set, adding..."
    echo "PubkeyAuthentication yes" >> /etc/ssh/sshd_config
    systemctl restart sshd
    echo "✓ SSH service restarted"
fi

# Ensure application directory ownership
if [ -d "/home/blaxium.com/canzim.blaxium.com" ]; then
    echo "Setting ownership of application directory..."
    chown -R blaxi2540:blaxi2540 /home/blaxium.com/canzim.blaxium.com
    echo "✓ Application directory ownership updated"
fi

echo ""
echo "================================================"
echo "✅ Setup complete!"
echo "================================================"
echo ""
echo "Test from your local machine:"
echo "ssh -i ~/.ssh/canzim-deploy blaxi2540@158.220.103.133 'echo Success!'"
echo ""
