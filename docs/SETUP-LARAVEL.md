# Setup Laravel in this repo

This repo already has Docker, Nginx, Postgres, and Redis. Use the script to scaffold Laravel 11, install Sanctum + Horizon, and run migrations.

Quick start
```bash
# 1) Scaffold Laravel + packages inside containers
bash scripts/init-laravel.sh

# 2) Bring everything up
docker compose up -d

# 3) Verify
curl http://localhost:8080/api/health
```

Key notes
- Sanctum SPA dev:
  - Set in `.env`: `SANCTUM_STATEFUL_DOMAINS=localhost:5173` and `SESSION_DOMAIN=localhost`
  - `APP_URL=http://localhost:8080`
- Queues/Horizon:
  - Queue: Redis (default via `.env`)
  - Horizon runs via `docker compose up -d horizon` (already part of compose)
- WebSockets (Soketi):
  - Starts on `:6001` if you enabled the `soketi` service in `docker-compose.yml`
  - Frontend connects via Pusher protocol (ws://localhost:6001)

Common commands
```bash
make up           # start stack
make down         # stop
make migrate      # run migrations
make horizon      # run horizon once (service also available in compose)
make pint         # lint
make test         # run test suite
```