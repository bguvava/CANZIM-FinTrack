#!/bin/bash
# Run this script as ROOT to setup blaxi2540 user for GitHub Actions deployment

echo "Setting up blaxi2540 user for deployment..."

# Check if user exists
if id "blaxi2540" &>/dev/null; then
    echo "✓ User blaxi2540 exists"
else
    echo "✗ User blaxi2540 does not exist!"
    echo "Creating user blaxi2540..."
    # Uncomment the next line if you need to create the user
    # useradd -m -s /bin/bash blaxi2540
fi

# Create .ssh directory
mkdir -p /home/blaxi2540/.ssh
chmod 700 /home/blaxi2540/.ssh

# Add GitHub Actions public key
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAID1XrwstGK818AoQyyRttrXUumCbdVqI6Z5lelaAjHA1 github-actions-canzim' >> /home/blaxi2540/.ssh/authorized_keys

# Set correct permissions
chmod 600 /home/blaxi2540/.ssh/authorized_keys
chown -R blaxi2540:blaxi2540 /home/blaxi2540/.ssh

# Ensure user has access to application directory
if [ -d "/home/blaxium.com/canzim.blaxium.com" ]; then
    chown -R blaxi2540:blaxi2540 /home/blaxium.com/canzim.blaxium.com
    echo "✓ Updated ownership of application directory"
fi

echo "✓ SSH key added to blaxi2540 successfully!"
echo ""
echo "Test the connection from your local machine:"
echo "ssh -i ~/.ssh/canzim-deploy blaxi2540@158.220.103.133"
