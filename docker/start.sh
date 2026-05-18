#!/bin/bash
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

php-fpm -D
nginx -g "daemon off;"