#!/usr/bin/env sh
set -e

# Entrypoint to fix permissions for Laravel writable directories and then exec the
# provided command. Idempotent and safe for WSL / Linux / CI environments.

# Ensure directories exist
mkdir -p /var/www/storage /var/www/bootstrap/cache /var/www/storage/logs

# Try to chown to www-data if available; ignore failures on platforms where this
# is not permitted (bind mounts on some systems). The || true keeps this safe.
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Ensure group/other write so the app user can write logs and cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# If a command was provided, run it (this allows CMD from Dockerfile to run).
if [ "$#" -gt 0 ]; then
  exec "$@"
else
  # Default to php-fpm if nothing else provided
  exec php-fpm
fi
