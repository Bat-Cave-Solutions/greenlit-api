#!/usr/bin/env bash
set -euo pipefail

# Tiny helper to run Laravel migrations inside the Docker app container.
# - Brings the stack up if needed
# - Ensures .env and APP_KEY exist
# - Runs php artisan migrate --force inside the app container

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

# Start only the required services (db, redis, app) to avoid port clashes with nginx
${DC} up -d db redis app

# Wait for the app container to be ready to accept commands
# We'll try a simple `php -v` inside the container with retries
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

# Ensure APP_KEY exists (key:generate is idempotent if already set)
${DC} exec -T "${APP_SERVICE}" php artisan key:generate --force >/dev/null || true

# Run migrations
${DC} exec -T "${APP_SERVICE}" php artisan migrate --force

echo "Migrations completed successfully."