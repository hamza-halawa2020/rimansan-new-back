#!/bin/sh
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public
chmod -R ug+rw storage bootstrap/cache

php artisan storage:link >/dev/null 2>&1 || true

if [ "${LARAVEL_OPTIMIZE:-true}" = "true" ]; then
    php artisan optimize:clear >/dev/null 2>&1 || true
    php artisan optimize >/dev/null 2>&1 || true
fi

exec "$@"
