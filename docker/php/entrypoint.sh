#!/bin/sh
set -e

# Wait for the database to accept connections
echo "Waiting for database..."
until php artisan db:show --no-interaction > /dev/null 2>&1; do
    sleep 2
done
echo "Database is ready."

# Discover packages (skipped during composer install --no-scripts)
php artisan package:discover --ansi

# Clear stale Blade/view cache
php artisan view:clear --ansi

# Create the storage symlink (needed for spatie/laravel-medialibrary)
php artisan storage:link --force --ansi

# Run any pending migrations
php artisan migrate --force --ansi

# Seed countries (uses updateOrCreate, safe to run on every boot)
php artisan db:seed --class=CountrySeeder --force --ansi

echo "Bootstrap complete."


exec "$@"
