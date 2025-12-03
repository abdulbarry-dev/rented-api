#!/bin/bash

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL to be ready..."
until pg_isready -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" > /dev/null 2>&1; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done

echo "PostgreSQL is ready!"

# Run migrations
php artisan migrate --force

# Seed database (optional, only for development)
if [ "$APP_ENV" = "local" ]; then
    php artisan db:seed --force
fi

# Clear and cache configuration
php artisan config:cache
php artisan route:cache

# Start Octane
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
