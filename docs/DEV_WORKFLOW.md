# Development Workflow

This guide documents the day-to-day workflow.. The goal is to keep Greenlit API stable, well tested, and continuously deployable.

## Guiding Principles

- Every change should include automated tests that cover the new or changed behaviour.
- Keep branches small and focused; ship incremental improvements instead of large drops.
- Run the same checks locally that GitHub Actions executes in CI so the pipeline stays green.

## One-Time Setup

1. Install Docker and Docker Compose.
2. Clone the repository and copy the environment file:
   ```bash
   git clone https://github.com/Bat-Cave-Solutions/greenlit-api.git
   cd greenlit-api
   cp env.example .env
   ```
3. Start the containers and initialise Laravel resources:
   ```bash
   docker compose up -d
   ./scripts/init-laravel.sh
   ```
4. Run the migrations and seeders the first time you set up the project:
   ```bash
   docker compose exec app php artisan migrate
   docker compose exec app php artisan db:seed
   ```

## Standard Feature Workflow

1. **Create a branch**
   ```bash
   git checkout -b feature/short-description
   ```
2. **Sync the environment** (only needed if dependencies changed):
   ```bash
   docker compose exec app composer install
   ```
3. **Write or update tests first**. For example, to add a unit test:
   ```bash
   docker compose exec app php artisan make:test Models/WidgetTest --unit
   ```
4. **Implement the code** and keep changes scoped to the branch goal.
5. **Run local quality checks** before committing:
   ```bash
   docker compose exec app vendor/bin/pint
   docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
   docker compose exec app php artisan test
   ```
   All three commands should succeed. Address any failures before moving on.
6. **Commit with a descriptive message** that explains the intent rather than the mechanics.
7. **Push and open a pull request**, noting any manual testing that was performed.

## Keeping Tests Green

- Extend factories or seed data so tests remain deterministic.
- When fixing a bug, add a regression test that fails before the fix and passes afterward.
- Prefer the in-memory SQLite database (as used in CI) for quick feedback; use the Docker database when you need PostgreSQL-specific behaviour.

## Working With CI

GitHub Actions runs the same sequence of checks defined above (`pint`, `phpstan`, and `php artisan test`). If the pipeline fails:

- Re-run the command locally to reproduce the failure.
- Check the workflow logs for stack traces or lint output.
- Fix the issue and push an additional commit; CI will re-run automatically.

Keeping CI green ensures the main branch is always releasable and saves everyone time during reviews.

## Additional References

- Project overview and setup instructions: `README.md`
- Architecture notes: `docs/emissions-hybrid-model.md`
- Detailed Laravel setup: `docs/SETUP-LARAVEL.md`
