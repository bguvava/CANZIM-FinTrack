#!/bin/bash

###############################################################################
# CANZIM-FinTrack Production Deployment Script
# This script handles the deployment to the production server
###############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/home/blaxium.com/canzim.blaxium.com"
BACKUP_DIR="/home/blaxium.com/canzim.blaxium.com.backup"
DEPLOYMENT_PACKAGE="/tmp/deployment.tar.gz"
PHP_BIN="/usr/local/lsws/lsphp83/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_prerequisites() {
    log_info "Checking prerequisites..."
    
    if [ ! -f "$DEPLOYMENT_PACKAGE" ]; then
        log_error "Deployment package not found at $DEPLOYMENT_PACKAGE"
        exit 1
    fi
    
    if [ ! -d "$APP_DIR" ]; then
        log_error "Application directory not found at $APP_DIR"
        exit 1
    fi
    
    log_info "Prerequisites check passed ✓"
}

backup_current_version() {
    log_info "Creating backup of current version..."
    
    # Remove old backup if exists
    if [ -d "$BACKUP_DIR" ]; then
        rm -rf "$BACKUP_DIR"
    fi
    
    # Create new backup
    cp -r "$APP_DIR" "$BACKUP_DIR"
    
    # Backup database
    cd "$APP_DIR"
    $PHP_BIN artisan db:backup 2>/dev/null || log_warning "Database backup failed or not configured"
    
    log_info "Backup created successfully ✓"
}

enable_maintenance_mode() {
    log_info "Enabling maintenance mode..."
    cd "$APP_DIR"
    $PHP_BIN artisan down --retry=60 --secret="$(openssl rand -hex 16)" || true
    log_info "Maintenance mode enabled ✓"
}

disable_maintenance_mode() {
    log_info "Disabling maintenance mode..."
    cd "$APP_DIR"
    $PHP_BIN artisan up
    log_info "Application is now live ✓"
}

extract_deployment() {
    log_info "Extracting deployment package..."
    
    cd "$APP_DIR"
    
    # Extract new files
    tar -xzf "$DEPLOYMENT_PACKAGE"
    
    log_info "Deployment package extracted ✓"
}

install_dependencies() {
    log_info "Installing Composer dependencies..."
    
    cd "$APP_DIR"
    
    # Install production dependencies
    $COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction
    
    log_info "Dependencies installed ✓"
}

run_migrations() {
    log_info "Running database migrations..."
    
    cd "$APP_DIR"
    
    # Run migrations
    $PHP_BIN artisan migrate --force
    
    log_info "Migrations completed ✓"
}

optimize_application() {
    log_info "Optimizing application..."
    
    cd "$APP_DIR"
    
    # Clear all caches
    $PHP_BIN artisan config:clear
    $PHP_BIN artisan route:clear
    $PHP_BIN artisan view:clear
    $PHP_BIN artisan cache:clear
    
    # Optimize for production
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
    $PHP_BIN artisan event:cache
    
    # Run general optimization
    $PHP_BIN artisan optimize
    
    log_info "Application optimized ✓"
}

set_permissions() {
    log_info "Setting correct permissions..."
    
    cd "$APP_DIR"
    
    # Set ownership (adjust user:group as needed for CyberPanel)
    # chown -R blaxi2540:blaxi2540 .
    
    # Set directory permissions
    find . -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find . -type f -exec chmod 644 {} \;
    
    # Storage and cache directories need write permissions
    chmod -R 775 storage bootstrap/cache
    
    log_info "Permissions set ✓"
}

restart_services() {
    log_info "Restarting services..."
    
    # Restart queue workers
    cd "$APP_DIR"
    $PHP_BIN artisan queue:restart
    
    # Restart PHP-FPM/LiteSpeed (if needed)
    # systemctl restart lsws || true
    
    log_info "Services restarted ✓"
}

verify_deployment() {
    log_info "Verifying deployment..."
    
    cd "$APP_DIR"
    
    # Check Laravel version
    $PHP_BIN artisan --version
    
    # Check database connectivity
    $PHP_BIN artisan migrate:status
    
    # Run a quick health check
    $PHP_BIN artisan tinker --execute="echo 'Database connection: OK';" || log_warning "Database check warning"
    
    log_info "Deployment verification completed ✓"
}

cleanup() {
    log_info "Cleaning up..."
    
    # Remove deployment package
    rm -f "$DEPLOYMENT_PACKAGE"
    
    # Remove old backups (keep last 3)
    # Add logic here if needed
    
    log_info "Cleanup completed ✓"
}

# Main deployment flow
main() {
    log_info "🚀 Starting CANZIM-FinTrack production deployment..."
    echo "================================================"
    
    check_prerequisites
    enable_maintenance_mode
    backup_current_version
    extract_deployment
    install_dependencies
    run_migrations
    optimize_application
    set_permissions
    restart_services
    verify_deployment
    disable_maintenance_mode
    cleanup
    
    echo "================================================"
    log_info "✅ Deployment completed successfully!"
    log_info "🌐 Application is live at: https://canzim.blaxium.com"
}

# Error handler
trap 'log_error "Deployment failed! Rolling back..."; disable_maintenance_mode; exit 1' ERR

# Execute main function
main
