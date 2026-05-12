#!/bin/bash
# Configure production .env file

cd /home/blaxium.com/canzim.blaxium.com

# Update application settings
sed -i 's/^APP_NAME=.*/APP_NAME="CANZIM FinTrack"/' .env
sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i 's|^APP_URL=.*|APP_URL=https://canzim.blaxium.com|' .env
sed -i 's/^APP_TIMEZONE=.*/APP_TIMEZONE=Africa\/Harare/' .env

# Update database settings
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=canzim_fintrack/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=canzim_user/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=C#dVqI6Z5lel@AjHA1/' .env

# Update logging for production
sed -i 's/^LOG_LEVEL=.*/LOG_LEVEL=error/' .env

echo "✓ Production .env configured successfully"
echo ""
echo "Verification:"
grep -E "^(APP_NAME|APP_ENV|APP_DEBUG|APP_URL|DB_DATABASE|DB_USERNAME)" .env
