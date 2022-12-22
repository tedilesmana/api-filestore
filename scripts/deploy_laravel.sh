#!/bin/bash

# Enter html directory
cd /var/www/api-gw/

# Create cache and chmod folders
mkdir -p /var/www/api-gw/bootstrap/cache
mkdir -p /var/www/api-gw/storage/framework/sessions
mkdir -p /var/www/api-gw/storage/framework/views
mkdir -p /var/www/api-gw/storage/framework/cache
mkdir -p /var/www/api-gw/public/files/

# Install dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install -d /var/www/api-gw/

# Copy configuration from /var/www/.env, see README.MD for more information
#cp /var/www/api-gw/.env.example /var/www/api-gw/.env

# Migrate all tables
php /var/www/api-gw/artisan migrate

# Clear any previous cached views
php /var/www/api-gw/artisan config:clear
php /var/www/api-gw/artisan cache:clear
php /var/www/api-gw/artisan view:clear

# Optimize the application
php /var/www/api-gw/artisan config:cache
php /var/www/api-gw/artisan optimize
#php /var/www/api-gw/artisan route:cache

# Change rights
chmod 777 -R /var/www/api-gw/bootstrap/cache
chmod 777 -R /var/www/api-gw/storage
chmod 777 -R /var/www/api-gw/public/files/

# Bring up application
php /var/www/api-gw/artisan up
