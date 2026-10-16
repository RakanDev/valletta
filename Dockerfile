# ---- 1) Build front-end assets (Tailwind + Alpine) ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---- 2) PHP 8.3 + Nginx (serversideup image, Laravel-ready) ----
FROM serversideup/php:8.3-fpm-nginx

USER root
RUN install-php-extensions intl
COPY --chmod=755 docker/60-seed.sh /etc/entrypoint.d/60-seed.sh
USER www-data

WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

# On every boot: migrate --force, storage:link, config/route/view cache
ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off

EXPOSE 8080
