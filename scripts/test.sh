#!/usr/bin/env bash
set -euo pipefail

# Tiny helper to run Laravel tests inside the Docker app container.
# Usage: bash scripts/test.sh [phpunit/php artisan test args]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="${SCRIPT_DIR}/.."
cd "${REPO_ROOT}"

DC="docker compose"
APP_SERVICE="app"

# Ensure .env exists
if [[ ! -f .env ]]; then
  if [[ -f env.example ]]; then
    cp -n env.example .env
    echo "Created .env from env.example"
  else
    echo "Missing .env and env.example; please create a .env first." >&2
    exit 1
  fi
fi

# Start only required services for tests (db, redis, app) to avoid port clashes with nginx
${DC} up -d --quiet-pull db redis app

# Wait for the app container to be ready to accept commands
RETRIES=30
SLEEP=2
READY=0
for ((i=1; i<=RETRIES; i++)); do
  if ${DC} exec -T "${APP_SERVICE}" php -v >/dev/null 2>&1; then
    READY=1
    break
  fi
  sleep "${SLEEP}"
  echo "Waiting for ${APP_SERVICE} container to be ready... (${i}/${RETRIES})"
done

if [[ "${READY}" -ne 1 ]]; then
  echo "App container not ready after $((RETRIES*SLEEP))s. Aborting." >&2
  exit 1
fi

# Ensure app key exists
${DC} exec -T "${APP_SERVICE}" php artisan key:generate --force >/dev/null || true

# Run tests; prefer php artisan test, but accept args passthrough
if [[ $# -eq 0 ]]; then
  ${DC} exec -T "${APP_SERVICE}" php artisan test
else
  ${DC} exec -T "${APP_SERVICE}" php artisan test "$@"
fi
