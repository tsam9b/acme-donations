#!/bin/bash
set -e

echo "Starting Acme Donations application..."

# Wait for database to be ready (if using MySQL)
# if [ "$DB_CONNECTION" = "mysql" ]; then
#     echo "Waiting for MySQL to be ready..."
#     while ! nc -z mysql 3306; do
#         sleep 1
#     done
#     echo "MySQL is ready!"
# fi

# Ensure SQLite database exists and has correct permissions
if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Setting up SQLite database..."
    mkdir -p /var/www/html/database

    # Remove old database to start fresh (avoids migration conflicts)
    if [ -f /var/www/html/database/database.sqlite ]; then
        echo "Removing existing database for fresh start..."
        rm -f /var/www/html/database/database.sqlite
    fi

    # Create new database
    echo "Creating fresh SQLite database..."
    touch /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    echo "SQLite database created successfully!"
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key if not set
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
    php artisan key:generate
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force --seed

# Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Application ready!"

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

