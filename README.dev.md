# Developer quickstart (WSL + Docker)

This project uses Docker for local development. The instructions below assume you're using WSL2 (recommended) and Docker Desktop with WSL integration enabled.

Why WSL2?
- Keeps files on a Linux filesystem (fast and correct POSIX permissions)
- Avoids Windows ACL issues with bind mounts (e.g. `storage`/`bootstrap/cache`)
- Works well with VS Code Remote - WSL

Quick start (first time after clone)

1. Open a WSL terminal and clone the repo inside your WSL filesystem (e.g. `~/code`):

   git clone <repo-url> ~/code/greenlit-api
   cd ~/code/greenlit-api

2. Ensure Docker Desktop WSL integration is enabled for your distro.
   - Docker Desktop -> Settings -> Resources -> WSL Integration -> Enable your distro (e.g., Ubuntu)

3. Build and start the stack:

   docker compose up -d --build

4. Install PHP dependencies and generate app key inside the `app` container:

   docker compose exec app bash -lc "composer install --no-interaction || true; cp .env.example .env || true; php artisan key:generate || true"

5. Verify permissions and run lint/tests:

   docker compose exec app bash -lc "stat -c '%U:%G %a' storage bootstrap/cache storage/logs || true"
   docker compose exec app bash -lc "vendor/bin/pint --test"

   # Run tests (fast, CI-like) using in-memory sqlite – same as CI
   composer test

   # Optional: run tests against Postgres service (integration)
   composer test:docker:pgsql

Viewing logs
- To follow the application logs in real time (useful after `docker compose up`):

  docker compose logs -f app

  You can also limit initial output with `--tail`, e.g. `docker compose logs --tail=200 -f app`.

Notes
- The image includes an idempotent entrypoint script (`.docker/entrypoint.sh`) that fixes permissions at container start so developers don't need to run `chown` themselves.
- Use VS Code Remote - WSL to edit files directly in WSL for best performance.
- If you run into permission issues, make sure your project folder is under your WSL home (not `/mnt/c/`).

Testing shortcuts
- Fast tests (SQLite, mirrors CI): `composer test`
- Postgres integration tests (uses the `db` service): `composer test:docker:pgsql`
- Migrations are driver-aware: Postgres-only features (JSONB, generated columns, GIN, CHECK constraints) are applied on Postgres and skipped on SQLite so fast tests can run.

CI parity (what runs in GitHub Actions)
- On PRs and pushes to main: a fast job uses SQLite in-memory, runs Pint, Larastan, and the test suite (equivalent to `composer test`).
- On pushes to main and nightly (04:00 UTC): a Postgres integration job starts Postgres + Redis, runs migrations, and then runs the same test suite against Postgres (similar to `composer test:docker:pgsql`).
- Rationale: Keep PRs fast while still catching DB-dialect issues regularly.

Troubleshooting
- If `git` complains about "dubious ownership" inside the container, run:

  docker compose exec app bash -lc "git config --global --add safe.directory /var/www"

- If Docker on Windows is set to use the Windows backend instead of WSL, enable WSL integration in Docker Desktop.
