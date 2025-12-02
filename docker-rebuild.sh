#!/bin/bash

echo "🐳 Fixing Docker Database Setup..."
echo ""

# Step 1: Stop containers
echo "1️⃣ Stopping containers and removing volumes..."
docker compose down -v

# Step 2: Remove local database
echo "2️⃣ Removing local database file..."
rm -f database/database.sqlite

# Step 3: Rebuild
echo "3️⃣ Rebuilding containers..."
docker compose up -d --build --detach

# Step 4: Show logs
echo "4️⃣ Showing logs (press Ctrl+C to exit)..."
echo ""
docker compose logs -f app

