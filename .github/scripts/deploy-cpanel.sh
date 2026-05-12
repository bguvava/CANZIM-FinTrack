#!/bin/bash

###############################################################################
# CANZIM-FinTrack cPanel Production Deployment Script
# Target: https://erp.canzimbabwe.org.zw
# Server: cloud783 (cPanel 134, Apache 2.4.67, MariaDB 10.11.16)
# cPanel Account: canzimba
###############################################################################

set -e  # Exit on any error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info()    { echo -e "${GREEN}[INFO]${NC}    $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC}   $1"; }
log_step()    { echo -e "${BLUE}[STEP]${NC}    $1"; }

# ---------------------------------------------------------------------------
# Configuration — ADJUST IF NEEDED
# ---------------------------------------------------------------------------
CPANEL_USER="canzimba"
APP_DIR="/home/${CPANEL_USER}/fintrack_app"
PUBLIC_DIR="${APP_DIR}/public"
BACKUP_BASE="/home/${CPANEL_USER}/fintrack_backups"
BACKUP_DIR="${BACKUP_BASE}/backup_$(date +%Y%m%d_%H%M%S)"
DEPLOYMENT_PACKAGE="/tmp/canzim-deployment.tar.gz"
ENV_FILE="${APP_DIR}/.env"
MAX_BACKUPS=5

# Detect PHP binary (cPanel EasyApache 4 common locations, PHP 8.3 / 8.2 / fallback)
detect_php() {
    for path in \
        "/opt/cpanel/ea-php83/root/usr/bin/php" \
        "/usr/local/php83/bin/php" \
        "/usr/local/bin/php83" \
        "$(which php8.3 2>/dev/null)" \
        "/opt/cpanel/ea-php82/root/usr/bin/php" \
        "/usr/local/php82/bin/php" \
        "/usr/local/bin/php82" \
        "$(which php8.2 2>/dev/null)" \
        "$(which php 2>/dev/null)"; do
        if [ -x "$path" ] 2>/dev/null; then
            echo "$path"
            return
        fi
    done
    log_error "PHP binary not found. Install PHP via cPanel EasyApache 4."
    exit 1
}

# Detect Composer binary
detect_composer() {
    for path in \
        "/usr/local/bin/composer" \
        "/usr/bin/composer" \
        "${HOME}/bin/composer" \
        "$(which composer 2>/dev/null)"; do
        if [ -x "$path" ] 2>/dev/null; then
            echo "$path"
            return
        fi
    done
    # Try to install Composer locally
    log_warning "Composer not found globally — attempting local install..."
    curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir=/usr/local/bin --filename=composer 2>/dev/null || true
    if [ -x "/usr/local/bin/composer" ]; then
        echo "/usr/local/bin/composer"
    else
        log_error "Composer not found. Install it manually."
        exit 1
    fi
}

PHP_BIN=$(detect_php)
COMPOSER_BIN=$(detect_composer)

log_info "PHP:      $PHP_BIN ($(${PHP_BIN} --version | head -1))"
log_info "Composer: $COMPOSER_BIN"
log_info "App Dir:  $APP_DIR"

# ---------------------------------------------------------------------------
check_prerequisites() {
    log_step "Checking prerequisites..."

    if [ ! -f "$DEPLOYMENT_PACKAGE" ]; then
        log_error "Deployment package not found: $DEPLOYMENT_PACKAGE"
        exit 1
    fi

    # Ensure app directory exists
    mkdir -p "$APP_DIR"
    mkdir -p "$BACKUP_BASE"

    # Check .env file exists on server (must be created before first deploy)
    if [ ! -f "$ENV_FILE" ]; then
        log_error ".env file not found at $ENV_FILE"
        log_error "Please create it from .github/deploy/.env.erp.template before deploying."
        exit 1
    fi

    log_info "Prerequisites check passed ✓"
}

backup_current_version() {
    log_step "Creating backup of current version..."

    if [ -d "$APP_DIR" ] && [ "$(ls -A "$APP_DIR" 2>/dev/null)" ]; then
        mkdir -p "$BACKUP_DIR"
        # Exclude storage/app/public (uploads) from backup to save space
        rsync -a --exclude='storage/app/public' --exclude='node_modules' "$APP_DIR/" "$BACKUP_DIR/"
        log_info "Backup created: $BACKUP_DIR ✓"

        # Remove old backups, keep only MAX_BACKUPS
        BACKUP_COUNT=$(ls -d ${BACKUP_BASE}/backup_* 2>/dev/null | wc -l)
        if [ "$BACKUP_COUNT" -gt "$MAX_BACKUPS" ]; then
            ls -td ${BACKUP_BASE}/backup_* | tail -n +$((MAX_BACKUPS + 1)) | xargs rm -rf
            log_info "Old backups cleaned (keeping last ${MAX_BACKUPS}) ✓"
        fi
    else
        log_info "No existing version to backup (first deploy)"
    fi
}

enable_maintenance_mode() {
    log_step "Enabling maintenance mode..."
    if [ -f "${APP_DIR}/artisan" ]; then
        "$PHP_BIN" "${APP_DIR}/artisan" down --retry=60 || true
        log_info "Maintenance mode enabled ✓"
    else
        log_info "First deploy — skipping maintenance mode"
    fi
}

disable_maintenance_mode() {
    log_step "Disabling maintenance mode..."
    "$PHP_BIN" "${APP_DIR}/artisan" up
    log_info "Application is live ✓"
}

extract_deployment() {
    log_step "Extracting deployment package..."
    cd "$APP_DIR"

    # Preserve .env and storage/app (uploaded files)
    cp "$ENV_FILE" /tmp/.env.preserve 2>/dev/null || true

    tar -xzf "$DEPLOYMENT_PACKAGE" --overwrite

    # Restore .env
    cp /tmp/.env.preserve "$ENV_FILE" 2>/dev/null || true
    rm -f /tmp/.env.preserve

    log_info "Deployment package extracted ✓"
}

install_dependencies() {
    log_step "Installing Composer dependencies..."
    cd "$APP_DIR"
    "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
    log_info "Composer dependencies installed ✓"
}

set_permissions() {
    log_step "Setting correct file permissions..."
    cd "$APP_DIR"

    # Standard permissions
    find . -type d -exec chmod 755 {} \;
    find . -type f -exec chmod 644 {} \;

    # Laravel writable directories
    chmod -R 775 storage bootstrap/cache

    # Make artisan executable
    chmod +x artisan

    log_info "Permissions set ✓"
}

setup_storage_link() {
    log_step "Setting up storage symlink..."
    cd "$APP_DIR"
    "$PHP_BIN" artisan storage:link --force || true
    log_info "Storage link created ✓"
}

run_migrations() {
    log_step "Running database migrations..."
    cd "$APP_DIR"
    "$PHP_BIN" artisan migrate --force
    log_info "Migrations completed ✓"
}

optimize_application() {
    log_step "Optimizing application for production..."
    cd "$APP_DIR"

    "$PHP_BIN" artisan config:clear
    "$PHP_BIN" artisan route:clear
    "$PHP_BIN" artisan view:clear
    "$PHP_BIN" artisan cache:clear
    "$PHP_BIN" artisan event:clear

    "$PHP_BIN" artisan config:cache
    "$PHP_BIN" artisan route:cache
    "$PHP_BIN" artisan view:cache
    "$PHP_BIN" artisan event:cache
    "$PHP_BIN" artisan optimize

    log_info "Application optimized ✓"
}

restart_services() {
    log_step "Restarting queue workers..."
    cd "$APP_DIR"
    "$PHP_BIN" artisan queue:restart || true
    log_info "Queue workers restarted ✓"
}

verify_deployment() {
    log_step "Verifying deployment..."
    cd "$APP_DIR"

    # Check Laravel responds
    "$PHP_BIN" artisan --version
    log_info "Laravel version check ✓"

    # Check DB connectivity
    "$PHP_BIN" artisan migrate:status | head -5
    log_info "Database connectivity ✓"

    log_info "Deployment verification completed ✓"
}

cleanup() {
    log_step "Cleaning up..."
    rm -f "$DEPLOYMENT_PACKAGE"
    log_info "Cleanup completed ✓"
}

# ---------------------------------------------------------------------------
# Main Execution
# ---------------------------------------------------------------------------
echo ""
echo "==========================================================="
echo "  CANZIM FinTrack — cPanel Production Deployment"
echo "  Target: https://erp.canzimbabwe.org.zw"
echo "==========================================================="
echo ""

check_prerequisites
backup_current_version
enable_maintenance_mode
extract_deployment
install_dependencies
set_permissions
setup_storage_link
run_migrations
optimize_application
restart_services
verify_deployment
disable_maintenance_mode
cleanup

echo ""
echo "==========================================================="
echo "  ✅ Deployment to erp.canzimbabwe.org.zw SUCCESSFUL!"
echo "  Deployed at: $(date)"
echo "==========================================================="
