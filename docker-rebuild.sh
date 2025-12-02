#!/bin/bash

echo "🐳 Fixing Docker Database Setup..."
echo ""

# 1) Stop containers and remove volumes
echo "1️⃣ Stopping containers and removing volumes..."
docker compose down -v

# 2) Remove local SQLite file (optional, for clean DB each time)
echo "2️⃣ Removing local database file..."
rm -f database/database.sqlite

# 3) Rebuild images
echo "3️⃣ Rebuilding containers..."
docker compose build --no-cache

# 4) Install PHP dependencies inside app container (bypassing entrypoint)
echo "4️⃣ Installing PHP dependencies with Composer inside container..."
docker compose run --rm --entrypoint "" app composer install

# 5) Start containers
echo "5️⃣ Starting containers..."
docker compose up -d

# 6) Show logs
echo "6️⃣ Showing logs (press Ctrl+C to exit)..."
echo ""
docker compose logs -f app