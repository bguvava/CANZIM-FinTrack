#!/bin/bash
# Deep SSH Authentication Diagnostic Script

echo "=========================================="
echo "SSH Authentication Deep Diagnostic"
echo "=========================================="
echo ""

echo "1. User Configuration:"
echo "----------------------"
getent passwd blaxi2540
echo ""

echo "2. Home Directory Ownership & Permissions:"
echo "-----------------------------------------"
ls -ld /home/blaxium.com
echo ""

echo "3. .ssh Directory Chain Permissions:"
echo "------------------------------------"
namei -l /home/blaxium.com/.ssh/authorized_keys
echo ""

echo "4. Authorized Keys Content:"
echo "---------------------------"
cat /home/blaxium.com/.ssh/authorized_keys 2>/dev/null || echo "File not found or not readable"
echo ""

echo "5. SSH Daemon StrictModes Setting:"
echo "----------------------------------"
grep -i "^StrictModes" /etc/ssh/sshd_config || echo "StrictModes not explicitly set (default: yes)"
echo ""

echo "6. SSH Daemon Key Authentication Settings:"
echo "-----------------------------------------"
grep -E "^(PubkeyAuthentication|AuthorizedKeysFile)" /etc/ssh/sshd_config
echo ""

echo "7. All SSH Authentication-Related Settings:"
echo "-------------------------------------------"
grep -v "^#" /etc/ssh/sshd_config | grep -E "Authentication|AuthorizedKeys|StrictModes" || echo "No explicit settings found"
echo ""

echo "8. Recent SSH Authentication Errors:"
echo "------------------------------------"
tail -50 /var/log/auth.log | grep "sshd.*blaxi2540.*publickey\|sshd.*blaxi2540.*Failed\|sshd.*blaxi2540.*error" | tail -10
echo ""

echo "9. SELinux Status:"
echo "-----------------"
getenforce 2>/dev/null || echo "SELinux not enabled"
echo ""

echo "10. Testing Key Fingerprint:"
echo "---------------------------"
su - blaxi2540 -c "ssh-keygen -l -f ~/.ssh/authorized_keys" 2>&1 || echo "Cannot verify key"
echo ""

echo "11. Checking if home directory is writable by group/others:"
echo "------------------------------------------------------------"
stat -c "Home Dir Permissions: %a %U:%G" /home/blaxium.com
echo ""

echo "12. Checking .ssh permissions:"
echo "------------------------------"
stat -c ".ssh Dir Permissions: %a %U:%G" /home/blaxium.com/.ssh 2>/dev/null || echo ".ssh not found"
stat -c "authorized_keys Permissions: %a %U:%G" /home/blaxium.com/.ssh/authorized_keys 2>/dev/null || echo "authorized_keys not found"
echo ""

echo "=========================================="
echo "Diagnostic Complete"
echo "=========================================="
