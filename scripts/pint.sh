#!/usr/bin/env bash
set -euo pipefail

# Tiny helper to run Laravel Pint inside the Docker app container.
# Usage: bash scripts/pint.sh [pint args]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="${SCRIPT_DIR}/.."
cd "${REPO_ROOT}"

DC="docker compose"
APP_SERVICE="app"

# Ensure .env exists (not strictly necessary for Pint, but keeps consistency)
if [[ ! -f .env ]]; then
  if [[ -f env.example ]]; then
    cp -n env.example .env
    echo "Created .env from env.example"
  fi
fi

# Start the stack (app depends on db+redis health; Pint only needs php in app)
${DC} up -d --quiet-pull

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

# Run Pint with passed arguments (default to --test if none provided)
if [[ $# -eq 0 ]]; then
  set -- --test
fi

${DC} exec -T "${APP_SERVICE}" vendor/bin/pint "$@"
