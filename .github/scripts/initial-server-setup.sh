#!/bin/bash

###############################################################################
# CANZIM-FinTrack Initial Server Setup Script
# Run this script on the production server for first-time setup
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   CANZIM-FinTrack Server Setup                ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo ""

# Configuration
APP_DIR="/home/blaxium.com/canzim.blaxium.com"
REPO_URL="https://github.com/bguvava/CANZIM-FinTrack.git"
PHP_BIN="/usr/local/lsws/lsphp83/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"
DB_NAME="canzim_fintrack"

echo -e "${YELLOW}This script will setup CANZIM-FinTrack on the production server${NC}"
echo -e "${YELLOW}Server: 158.220.103.133${NC}"
echo -e "${YELLOW}Domain: https://canzim.blaxium.com${NC}"
echo ""
read -p "Continue? (yes/no): " -r
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Setup cancelled."
    exit 1
fi

echo ""
echo -e "${GREEN}[1/10] Checking prerequisites...${NC}"
# Check if PHP is available
if ! command -v $PHP_BIN &> /dev/null; then
    echo -e "${RED}PHP not found at $PHP_BIN${NC}"
    exit 1
fi

# Check if Composer is available
if ! command -v $COMPOSER_BIN &> /dev/null; then
    echo -e "${YELLOW}Composer not found, installing...${NC}"
    curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir=/usr/local/bin --filename=composer
fi

# Check if Git is available
if ! command -v git &> /dev/null; then
    echo -e "${RED}Git is not installed. Please install Git first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Prerequisites check passed${NC}"

echo ""
echo -e "${GREEN}[2/10] Creating application directory...${NC}"
if [ -d "$APP_DIR" ]; then
    echo -e "${YELLOW}Directory already exists. Backing up...${NC}"
    mv "$APP_DIR" "${APP_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
fi
mkdir -p "$APP_DIR"
echo -e "${GREEN}✓ Directory created${NC}"

echo ""
echo -e "${GREEN}[3/10] Cloning repository...${NC}"
cd "$APP_DIR"
git clone "$REPO_URL" .
echo -e "${GREEN}✓ Repository cloned${NC}"

echo ""
echo -e "${GREEN}[4/10] Installing Composer dependencies...${NC}"
cd "$APP_DIR"
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"

echo ""
echo -e "${GREEN}[5/10] Setting up environment file...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${YELLOW}⚠ Please edit .env file with production settings${NC}"
    echo -e "${YELLOW}⚠ Run: nano $APP_DIR/.env${NC}"
else
    echo -e "${YELLOW}.env file already exists, skipping...${NC}"
fi

echo ""
echo -e "${GREEN}[6/10] Generating application key...${NC}"
$PHP_BIN artisan key:generate --force
echo -e "${GREEN}✓ Application key generated${NC}"

echo ""
echo -e "${GREEN}[7/10] Setting up database...${NC}"
echo -e "${YELLOW}Database setup requires manual configuration${NC}"
echo ""
echo "Please create the database and user:"
echo -e "${BLUE}mysql -u root -p${NC}"
echo -e "${BLUE}CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;${NC}"
echo -e "${BLUE}CREATE USER 'canzim_user'@'localhost' IDENTIFIED BY 'your_secure_password';${NC}"
echo -e "${BLUE}GRANT ALL PRIVILEGES ON $DB_NAME.* TO 'canzim_user'@'localhost';${NC}"
echo -e "${BLUE}FLUSH PRIVILEGES;${NC}"
echo -e "${BLUE}EXIT;${NC}"
echo ""
read -p "Have you created the database? (yes/no): " -r
if [[ $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo -e "${GREEN}[8/10] Running migrations...${NC}"
    $PHP_BIN artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Skipping migrations. Run manually: php artisan migrate --force${NC}"
fi

echo ""
echo -e "${GREEN}[9/10] Setting up storage...${NC}"
$PHP_BIN artisan storage:link
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Storage configured${NC}"

echo ""
echo -e "${GREEN}[10/10] Optimizing application...${NC}"
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan optimize
echo -e "${GREEN}✓ Application optimized${NC}"

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}✅ Server setup completed!${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit .env file: nano $APP_DIR/.env"
echo "2. Update database credentials in .env"
echo "3. Update mail settings in .env"
echo "4. Build frontend assets (locally then deploy, or on server):"
echo "   - npm install"
echo "   - npm run build"
echo "5. Test the application: https://canzim.blaxium.com"
echo "6. Setup queue workers with supervisor (if needed)"
echo "7. Setup cron jobs for scheduled tasks (if needed)"
echo ""
echo -e "${GREEN}Application directory: $APP_DIR${NC}"
echo -e "${GREEN}Application URL: https://canzim.blaxium.com${NC}"
echo ""
