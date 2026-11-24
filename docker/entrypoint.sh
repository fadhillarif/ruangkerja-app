#!/bin/sh
set -eu

APP_PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force
exec apache2-foreground
