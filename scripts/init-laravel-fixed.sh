#!/usr/bin/env bash
set -euo pipefail

echo "🚀 Initializing Laravel 11 with Docker..."

# 1) Create Laravel skeleton in a temp directory if composer.json doesn't exist
if [ ! -f "composer.json" ]; then
  echo "📦 Scaffolding Laravel 11..."
  
  # Create temporary directory for Laravel installation
  TEMP_DIR=$(mktemp -d)
  echo "Creating Laravel project in temporary directory: $TEMP_DIR"
  
  # Create Laravel project in temp directory
  docker run --rm -u $(id -u):$(id -g) -v "$TEMP_DIR":/app -w /app composer:2 create-project laravel/laravel:^11.0 .
  
  # Copy essential Laravel files to current directory
  echo "📁 Copying Laravel core files..."
  cp "$TEMP_DIR/composer.json" .
  cp "$TEMP_DIR/composer.lock" .
  cp "$TEMP_DIR/artisan" .
  cp "$TEMP_DIR/phpunit.xml" .
  cp -n "$TEMP_DIR/.env.example" . || true
  
  # Copy Laravel directories, preserving existing custom files
  for dir in bootstrap config database public resources storage tests vendor; do
    if [ ! -d "$dir" ]; then
      cp -r "$TEMP_DIR/$dir" .
    fi
  done
  
  # Handle app directory merge
  if [ ! -d "app/Models" ]; then
    mkdir -p app/Models
    cp -r "$TEMP_DIR/app/Models"/* app/Models/ 2>/dev/null || true
  fi
  if [ ! -f "app/Http/Controllers/Controller.php" ]; then
    mkdir -p app/Http/Controllers
    cp -r "$TEMP_DIR/app/Http/Controllers"/* app/Http/Controllers/ 2>/dev/null || true
  fi
  
  # Handle routes directory merge
  if [ ! -f "routes/web.php" ]; then
    cp "$TEMP_DIR/routes/web.php" routes/
  fi
  if [ ! -f "routes/console.php" ]; then
    cp "$TEMP_DIR/routes/console.php" routes/
  fi
  
  # Clean up
  rm -rf "$TEMP_DIR"
  
  echo "✅ Laravel scaffolding completed, preserving existing custom files."
fi

# 2) Set up environment file
echo "⚙️  Setting up environment..."
cp -n .env.example .env || true

# 3) Build and start Docker services
echo "🐳 Building and starting Docker services..."
docker compose up -d --build

# 4) Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# 5) Install required packages
echo "📦 Installing Laravel packages..."
docker compose exec app composer require laravel/sanctum --no-interaction || true
docker compose exec app composer require laravel/horizon --no-interaction || true
docker compose exec app composer require laravel/pint --dev --no-interaction || true

# 6) Laravel application setup
echo "🔧 Configuring Laravel application..."
docker compose exec app php artisan key:generate || true
docker compose exec app php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag="sanctum-migrations" || true
docker compose exec app php artisan migrate --force || true
docker compose exec app php artisan horizon:install || true

# 7) Fix permissions
echo "🔐 Setting proper file permissions..."
docker compose exec app chown -R www-data:www-data storage bootstrap/cache || true
docker compose exec app chmod -R 775 storage bootstrap/cache || true

# 8) Clear caches
echo "🧹 Clearing application caches..."
docker compose exec app php artisan route:clear || true
docker compose exec app php artisan config:clear || true
docker compose exec app php artisan view:clear || true

echo ""
echo "🎉 Laravel setup completed successfully!"
echo ""
echo "🌐 Your API is available at: http://localhost:8080"
echo "💚 Health check: http://localhost:8080/api/health"
echo "📊 Horizon dashboard: http://localhost:8080/horizon"
echo ""
echo "🚀 Happy coding!"