#!/bin/sh
# Seeds the admin account + catalog when SEED_ON_DEPLOY=true.
# Set it for the first deploy only, then remove the variable.
if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
    echo "Valletta: running db:seed"
    php /var/www/html/artisan db:seed --force
fi

# Convert any product photo not yet in the 4:5 standard (skips done ones).
# Runs in the background so the site starts right away.
echo "Valletta: normalizing product images in the background"
nohup php /var/www/html/artisan valletta:normalize-images > /tmp/normalize-images.log 2>&1 &
