#!/usr/bin/env bash
set -euo pipefail

# Alternative approach: Create Laravel in temp directory and merge
if [ ! -f "composer.json" ]; then
  echo "Scaffolding Laravel 11..."
  
  # Create temporary directory
  TEMP_DIR=$(mktemp -d)
  echo "Creating Laravel project in temporary directory: $TEMP_DIR"
  
  # Create Laravel project in temp directory
  docker run --rm -u $(id -u):$(id -g) -v "$TEMP_DIR":/app -w /app composer:2 create-project laravel/laravel:^11.0 .
  
  # Copy essential Laravel files to current directory
  echo "Copying Laravel files..."
  cp "$TEMP_DIR/composer.json" .
  cp "$TEMP_DIR/composer.lock" .
  cp "$TEMP_DIR/artisan" .
  cp "$TEMP_DIR/phpunit.xml" .
  cp -n "$TEMP_DIR/.env.example" . || true
  
  # Copy Laravel directories, preserving existing custom files
  if [ ! -d "bootstrap" ]; then
    cp -r "$TEMP_DIR/bootstrap" .
  fi
  if [ ! -d "config" ]; then
    cp -r "$TEMP_DIR/config" .
  fi
  if [ ! -d "database" ]; then
    cp -r "$TEMP_DIR/database" .
  fi
  if [ ! -d "public" ]; then
    cp -r "$TEMP_DIR/public" .
  fi
  if [ ! -d "resources" ]; then
    cp -r "$TEMP_DIR/resources" .
  fi
  if [ ! -d "storage" ]; then
    cp -r "$TEMP_DIR/storage" .
  fi
  if [ ! -d "tests" ]; then
    cp -r "$TEMP_DIR/tests" .
  fi
  if [ ! -d "vendor" ]; then
    cp -r "$TEMP_DIR/vendor" .
  fi
  
  # Merge app directory (preserve existing controllers, etc.)
  if [ ! -d "app/Models" ]; then
    mkdir -p app/Models
    cp -r "$TEMP_DIR/app/Models"/* app/Models/
  fi
  
  # Merge routes (preserve existing api.php)
  if [ ! -f "routes/web.php" ]; then
    cp "$TEMP_DIR/routes/web.php" routes/
  fi
  if [ ! -f "routes/console.php" ]; then
    cp "$TEMP_DIR/routes/console.php" routes/
  fi
  
  # Clean up
  rm -rf "$TEMP_DIR"
  
  echo "Laravel scaffolding completed, preserving existing custom files."
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