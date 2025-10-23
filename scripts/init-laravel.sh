#!/usr/bin/env bash
set -euo pipefail

# 1) Create Laravel skeleton in the repo BEFORE building the image
if [ ! -f "composer.json" ]; then
  echo "Scaffolding Laravel 11..."
  
  # Backup existing app and routes directories if they exist
  if [ -d "app" ]; then
    echo "Backing up existing app directory..."
    mv app app.backup
  fi
  if [ -d "routes" ]; then
    echo "Backing up existing routes directory..."
    mv routes routes.backup
  fi
  
  # Create Laravel project
  docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app composer:2 create-project laravel/laravel:^11.0 .
  
  # Restore custom files from backups
  if [ -d "app.backup" ]; then
    echo "Restoring custom app files..."
    cp -r app.backup/* app/ 2>/dev/null || true
    rm -rf app.backup
  fi
  if [ -d "routes.backup" ]; then
    echo "Restoring custom routes files..."
    cp -r routes.backup/* routes/ 2>/dev/null || true
    rm -rf routes.backup
  fi
fi

# 2) Require core packages
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app composer:2 require \
  laravel/sanctum laravel/horizon laravel/pint --no-interaction

# 3) Env + start services
cp -n .env.example .env || true
docker compose up -d

# 4) App setup
docker compose exec app php artisan key:generate || true
docker compose exec app php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag="sanctum-migrations" || true
docker compose exec app php artisan migrate || true
docker compose exec app php artisan horizon:install || true

echo "Done. Visit: http://localhost:8080/api/health"