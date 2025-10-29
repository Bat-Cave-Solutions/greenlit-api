# greenlit-api

For local development (WSL + Docker) see `README.dev.md` for a quickstart, troubleshooting tips, and the `docker compose logs -f app` command to follow application logs.

## Prerequisites

- Docker and Docker Compose
- Composer (for installing PHP dependencies)
- Node.js (only if you plan to build assets locally)

## Quickstart

1. Copy env and install deps
	- `cp .env.example .env`
	- `composer install`
	- `php artisan key:generate`
2. Start services
	- `docker compose up -d`
3. Run migrations
	- `php artisan migrate`

Service ports (default)
- Postgres: 5432 (CI), Docker `db` service mapped to `${DB_PORT_HOST:-55432}` on host by default
- Redis: 6379
- App via nginx: http://localhost:8080

## Tests and formatting

- Run tests (SQLite, fast): `composer test`
- Run tests (Postgres via Docker): `composer test:docker:pgsql`
- Lint (Pint): `composer lint` (check), `composer lint:fix` (fix)
- Static analysis (Larastan): `composer stan`

## Testing

- Fast (CI-like) tests using SQLite in-memory:
	- `composer test`

- Optional Postgres integration tests (runs inside the app container against the `db` service):
	- `composer test:docker:pgsql`

Migrations are driver-aware so Postgres-only features (JSONB, generated columns, GIN, and CHECK constraints) are applied on Postgres and skipped on SQLite, keeping the fast test path green.

## CI strategy (how it works)

We keep PRs fast and still validate Postgres-specific behavior on a schedule:

- Fast job (SQLite) — runs on pull_request and push to main
	- Installs deps, copies `.env`, generates app key
	- Lints with Pint, runs Larastan
	- Runs tests with SQLite in-memory (mirrors `composer test`)

- Postgres integration job — runs on push to main and nightly at 04:00 UTC
	- Brings up Postgres 16 and Redis as services
	- Installs deps, copies `.env`, generates app key
	- Runs `php artisan migrate --force` against Postgres
	- Runs the same test suite against Postgres (catches DB-dialect issues)

Local equivalents
- PR-fast path: `composer test` (SQLite)
- Postgres integration: `composer test:docker:pgsql` (uses the `db` service)

Why this approach
- PRs stay quick (minutes), while a scheduled Postgres run ensures generated columns, GIN indexes, and CHECK constraints behave correctly on the real database.