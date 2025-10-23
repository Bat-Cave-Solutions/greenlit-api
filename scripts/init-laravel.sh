#!/usr/bin/env bash
set -euo pipefail

# Bootstrap supporting services
echo "Starting containers (db, redis, app)..."
docker compose up -d db redis app

# Create Laravel 11 skeleton in a temp dir inside the app container, then merge into repo root
echo "Scaffolding Laravel 11..."
docker compose run --rm app sh -lc '
  set -e
  # Create in temp folder to avoid overwriting existing files (docker-compose, Dockerfile, etc.)
  composer create-project laravel/laravel:^11.0 /var/www/laravel-tmp

  # Move all files (including dotfiles) from laravel-tmp into /var/www
  # Busybox mv: use find to move contents safely
  find /var/www/laravel-tmp -mindepth 1 -maxdepth 1 -exec mv -f {} /var/www/ \;
  rm -rf /var/www/laravel-tmp

  # Ensure vendor present and install core packages
  composer install --no-interaction --prefer-dist

  # Add packages (Sanctum, Horizon, Pint)
  composer require laravel/sanctum laravel/horizon laravel/pint --no-interaction

  # Generate app key and run Horizon install
  cp -n .env.example .env || true
  php artisan key:generate
  php artisan horizon:install || true

  # Try to publish Sanctum migrations if needed (Laravel 11 often autoloads)
  php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag="sanctum-migrations" --force || true

  # Cache drivers/queue set via .env; do a best-effort migrate
  php artisan migrate || true
'

echo
echo "Done. Next steps:"
echo "1) Check .env for SANCTUM_STATEFUL_DOMAINS=localhost:5173 and SESSION_DOMAIN=localhost"
echo "2) Start the full stack: docker compose up -d"
echo "3) API: http://localhost:8080, Health: http://localhost:8080/api/health"
echo "4) Horizon dashboard (when routed): /horizon"