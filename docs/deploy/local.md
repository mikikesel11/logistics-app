# Local Deployment Runbook

How to run the whole app — Laravel API + React SPA — on your own machine.

No Docker, MySQL, or Redis is required: the API defaults to **SQLite** with
`array`/`sync` drivers. Docker is optional, only for MySQL + Redis prod parity
(see [the last section](#optional-mysql--redis-parity-via-docker)).

## Prerequisites

| Tool | Version | Used by |
|---|---|---|
| PHP | 8.3+ (8.4 recommended) | `api/` |
| Composer | 2.x | `api/` |
| Node | 20.19+ or 22.12+ | `web/` (Vite 7/8) |
| npm | bundled with Node | `web/` |
| Docker | optional | MySQL + Redis parity only |

Quick check:

```bash
php -v && composer --version && node -v && npm -v
```

## One-time setup

### API (`api/`)

```bash
cd api
composer install
cp .env.example .env         # SQLite + sync/array drivers by default
php artisan key:generate
php artisan migrate --seed   # creates + seeds the SQLite DB
```

The seeder creates a login you'll use everywhere:

- **Email:** `admin@example.com`
- **Password:** `password`

### Web (`web/`)

```bash
cd ../web
npm install
cp .env.example .env         # optional; defaults work for local dev
```

## Running it (two terminals)

**Terminal 1 — API** (`http://localhost:8000`):

```bash
cd api
php artisan serve
```

**Terminal 2 — Web SPA** (`http://localhost:5173`):

```bash
cd web
npm run dev
```

Open **http://localhost:5173** and log in with `admin@example.com` / `password`.

The SPA proxies `/api/*` to the Laravel server (default `http://localhost:8000`,
override with `VITE_API_PROXY` in `web/.env`), so both live on one origin and no
CORS config is needed — the same shape as production.

## One-command API (optional)

From `api/`, this runs the server, queue listener, log tailer, and Vite together:

```bash
cd api
composer dev
```

> **Note:** `composer dev` includes `php artisan pail` (log tailer), which
> misbehaves when its output is piped or captured non-interactively. If you're
> running in a non-interactive/agent/CI shell, prefer the plain two-terminal
> `php artisan serve` + `npm run dev` flow above instead.

The React SPA in `web/` is still a separate `npm run dev` — `composer dev`'s
Vite step serves the API's own bundled assets, not the SPA.

## Resetting the database

Wipe and re-seed the local SQLite DB at any time:

```bash
cd api
php artisan migrate:fresh --seed
```

## Running the tests

**API (Pest):**

```bash
cd api
php artisan test
```

**Web (Playwright E2E):** the suite starts Vite on `:5173` itself, but it needs
a fresh, seeded API running on `:8000`.

```bash
# Terminal 1 — seeded API
cd api
php artisan migrate:fresh --seed && php artisan serve

# Terminal 2 — E2E
cd web
npx playwright install chromium   # first run only
npm run test:e2e
```

The spec logs in, creates a customer + carrier, links them on a load, walks the
load through its status lifecycle, and generates + downloads a BOL PDF.

## Web build (production-style, local)

```bash
cd web
npm run build      # tsc -b + Vite build → dist/
npm run preview    # serve the built assets locally
```

## Ports & URLs

| Service | URL |
|---|---|
| Laravel API | http://localhost:8000 |
| React SPA (Vite dev) | http://localhost:5173 |
| MySQL (Docker, optional) | localhost:3306 |
| Redis (Docker, optional) | localhost:6379 |

## Troubleshooting

- **`APP_KEY` / decryption errors** → you skipped `php artisan key:generate`.
- **Login fails with a fresh DB** → run `php artisan migrate --seed` (or
  `migrate:fresh --seed`) to create the seeded admin.
- **SPA shows network/404 on `/api/*`** → the API isn't running on `:8000`, or
  `VITE_API_PROXY` points elsewhere. Start `php artisan serve` first.
- **Playwright can't find a browser** → run `npx playwright install chromium`.
- **Node version rejected by Vite** → upgrade to Node 20.19+ or 22.12+.

## Optional: MySQL + Redis parity via Docker

Production (EcoWebHosting) runs MySQL 8.4 + Redis. To mirror that locally:

```bash
docker compose up -d          # starts logistics-mysql + logistics-redis
```

Then point `api/.env` at them (the example file has these commented out):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logistics
DB_USERNAME=logistics
DB_PASSWORD=secret

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

Re-run `php artisan migrate --seed` afterward to populate MySQL. With the Redis
queue driver, drain jobs with `php artisan queue:work` (production drains it via
cron — see [ecowebhosting.md](ecowebhosting.md)).

Tear down with `docker compose down` (add `-v` to also drop the data volumes).

---

See also: [root README](../../README.md) · [api/README](../../api/README.md) ·
[web/README](../../web/README.md) · [production deploy](ecowebhosting.md).
