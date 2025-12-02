# Docker Setup for Acme Donations

This project includes a complete Docker setup for easy deployment and development.

## Prerequisites

- Docker (20.10+)
- Docker Compose (2.0+)

## Quick Start

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd acme-donations
   ```

2. **Build and start the containers**:
   ```bash
   docker-compose up -d --build
   ```

3. **Access the application**:
   - Open your browser and navigate to: `http://localhost:8080`

4. **Stop the containers**:
   ```bash
   docker-compose down
   ```

## Services

The Docker setup includes the following services:

- **app**: PHP 8.2-FPM + Nginx + Laravel application
- **node**: Node.js 20 for building frontend assets (Vue + Vite)
- **mysql** (optional): MySQL 8.0 database server

## Environment Configuration

The application uses SQLite by default for simplicity. If you want to use MySQL:

1. Uncomment the `mysql` service in `docker-compose.yml`
2. Update the `app` service environment variables:
   ```yaml
   environment:
     - DB_CONNECTION=mysql
     - DB_HOST=mysql
     - DB_PORT=3306
     - DB_DATABASE=acme_donations
     - DB_USERNAME=acme_user
     - DB_PASSWORD=secret
   ```

## Development Workflow

### Running Commands Inside Containers

**Laravel Artisan commands**:
```bash
docker-compose exec app php artisan <command>
```

**Composer commands**:
```bash
docker-compose exec app composer <command>
```

**NPM commands** (for frontend development):
```bash
docker-compose exec node npm <command>
```

### Watching Frontend Changes

For development with hot reload:
```bash
docker-compose exec node npm run dev
```

### Running Migrations

```bash
docker-compose exec app php artisan migrate
```

### Seeding Database

```bash
docker-compose exec app php artisan db:seed
```

### Clearing Caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## Mailcatcher (Email Testing)

All emails sent by the application are captured by Mailcatcher. You can view them in the web interface:

**Access Mailcatcher Web UI:**
- Open your browser: `http://localhost:1080`

**How it works:**
- The app is configured to send all emails to Mailcatcher (SMTP: mailcatcher:1025)
- No emails are actually sent to real email addresses
- All emails are captured and displayed in the web interface
- Perfect for testing donation receipts, password resets, etc.

**Example - Test email functionality:**
```bash
# Trigger a test email (if you have a command for it)
docker compose exec app php artisan tinker
# Then in tinker:
Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });
# Check http://localhost:1080 to see the email
```

## Logs

View application logs:
```bash
docker-compose logs -f app
```

View all logs:
```bash
docker-compose logs -f
```

## Troubleshooting

### Permission Issues

If you encounter permission issues:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Rebuilding Containers

If you need to rebuild from scratch:
```bash
docker-compose down -v
docker-compose up -d --build
```

### Database Connection Issues

Make sure the database file exists and has proper permissions:
```bash
docker-compose exec app touch /var/www/html/database/database.sqlite
docker-compose exec app chmod 664 /var/www/html/database/database.sqlite
```

## Production Deployment

For production deployment:

1. Update `.env` with production values
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use proper database credentials
4. Build assets for production:
   ```bash
   docker compose exec node npm run build
   ```
5. Use a reverse proxy (e.g., Traefik, Nginx) in front of the app container
6. Consider using Docker secrets for sensitive data

## File Structure

```
docker/
├── nginx/
│   └── default.conf      # Nginx server configuration
├── supervisord.conf      # Supervisor configuration (manages PHP-FPM + Nginx)
└── entrypoint.sh        # Container initialization script
Dockerfile               # Main application container
docker-compose.yml       # Docker Compose configuration
.dockerignore           # Files to exclude from Docker build
```

## Notes

- The application runs on port 8080 by default (configurable in docker-compose.yml)
- SQLite database is stored in `database/database.sqlite`
- Frontend assets are built during container startup
- The entrypoint script automatically runs migrations and seeds on first start

