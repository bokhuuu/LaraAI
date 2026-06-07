# Container startup script - runs once when the container boots.
# 1. Fix storage permissions for www-data
# 2. Start Horizon queue worker in background
# 3. Start PHP-FPM and Nginx in foreground

#!/bin/bash
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

php artisan horizon &

php-fpm -D
nginx -g "daemon off;"