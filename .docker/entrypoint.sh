#!/usr/bin/env sh
set -e

# Entrypoint to fix permissions for Laravel writable directories and then exec
# the provided command. Idempotent and safe for WSL / Linux / CI environments.

log() {
  printf "[entrypoint] %s\n" "$*"
}

log "starting entrypoint (pid $$)"
log "CMD: $*"
log "PUID=${PUID:-unset} PGID=${PGID:-unset} APP_ENV=${APP_ENV:-unset} APP_DEBUG=${APP_DEBUG:-unset}"

# Show ownership before changes
log "ownership before changes:"
ls -ld /var/www /var/www/storage /var/www/bootstrap/cache /var/www/storage/logs 2>/dev/null || true
stat -c '%n %U:%G %a' /var/www/storage /var/www/bootstrap/cache /var/www/storage/logs 2>/dev/null || true

# Ensure directories exist
mkdir -p /var/www/storage /var/www/bootstrap/cache /var/www/storage/logs

# Attempt to change ownership to www-data (safe to fail on some mounts)
if chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null; then
  log "chown to www-data succeeded"
else
  log "chown to www-data failed or was not permitted; continuing"
fi

# Ensure group/other write so the app user can write logs and cache
if chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null; then
  log "chmod 775 applied"
else
  log "chmod failed; continuing"
fi

# Show ownership after changes
log "ownership after changes:"
stat -c '%n %U:%G %a' /var/www/storage /var/www/bootstrap/cache /var/www/storage/logs 2>/dev/null || true

# Execute provided command (or default to php-fpm)
if [ "$#" -gt 0 ]; then
  log "exec: $*"
  exec "$@"
else
  log "exec: php-fpm (default)"
  exec php-fpm
fi
