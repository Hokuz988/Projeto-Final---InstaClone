#!/usr/bin/env bash
set -e

cd /app/src

mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache

until bash -c "echo > /dev/tcp/${DB_HOST:-mysql}/3306" 2>/dev/null; do
  sleep 2
done

php artisan storage:link --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
