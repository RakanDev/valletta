#!/bin/sh
# Seeds the admin account + first 4 models when SEED_ON_DEPLOY=true.
# Set it for the first deploy only, then remove the variable.
if [ "${SEED_ON_DEPLOY:-false}" = "true" ]; then
    echo "Valletta: running db:seed"
    php /var/www/html/artisan db:seed --force
fi
