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

## Services

`docker compose up -d` starts the full stack by default:

- App (PHP-FPM)
- Nginx (on :8080)
- Postgres (db, published on host :5434 by default)
- Redis (cache/queues)
- Horizon (queue worker + dashboard)
- Soketi (Pusher-compatible WebSocket on :6001)

You can always see what’s running:

```bash
docker compose ps
```

### Changing the host DB port

The Postgres container listens on 5432 internally, and is published to your host using the `DB_PORT_HOST` variable from `.env`:

```env
DB_PORT_HOST=5434
```

To use a different host port (e.g., 5433 or 55432), update `DB_PORT_HOST` and restart:

```bash
docker compose up -d
```

Inside containers, keep `DB_HOST=db` and `DB_PORT=5432` — only the host-facing port changes.