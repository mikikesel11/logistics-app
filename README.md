# Logistics App

A freight brokerage operating system — the software a broker runs their whole
business on. Long-term it spans four pillars:

1. **Load-board aggregation** (DAT et al.) — *deferred behind an adapter*
2. **Bill of Lading** generation
3. **CRM** for shippers (customers) and carriers
4. **Unified comms** inbox (SMS / email / calls) — *later pillar*

It is functionally a **TMS + CRM + communications hub**. The MVP delivers the
CRM + Bill of Lading core end to end.

## Architecture

- **`api/`** — Laravel (PHP) API-first backend. The single JSON API all clients
  share. See [ADR 0001](docs/adr/0001-api-first-laravel-react.md).
- **`web/`** — React + TypeScript SPA that consumes the API. Built to static
  assets; native iOS/Android come later off the same API.
- Multi-tenant-ready schema, single tenant for now
  ([ADR 0002](docs/adr/0002-single-tenant-now-multi-later.md)).
- Production runs on **EcoWebHosting shared hosting**, which drives several
  stack choices ([ADR 0003](docs/adr/0003-shared-hosting-constraints.md)).

## Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13.x, PHP 8.4+ |
| DB | MySQL 8.4 (prod) · SQLite (local/CI) |
| Auth | Laravel Sanctum |
| Roles | spatie/laravel-permission |
| Cache/session/queue | Redis (prod) · array/sync (local); queue drained by cron |
| PDF | barryvdh/laravel-dompdf |
| Web | React 19 + TypeScript + Vite + Tailwind (shadcn-style primitives) |
| Tests | Pest (API) · Playwright (web E2E) |

## Local development

The local machine needs **no Docker/MySQL/Redis** — the API defaults to SQLite
and array/sync drivers. (If you have Docker and want MySQL + Redis parity, run
`docker compose up -d` and point `api/.env` at them.)

```bash
# API
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan test          # Pest
php artisan serve         # http://localhost:8000

# Web
cd ../web
npm install
npm run dev               # http://localhost:5173 (proxies /api → :8000)
npm run test:e2e          # Playwright: login → CRM → Load → BOL
```

The web app calls `/api/*` on its own origin (dev proxy, or Laravel `public/`
in production), so it needs no CORS config. More detail in
[web/README.md](web/README.md).

## Deployment

See [docs/deploy/ecowebhosting.md](docs/deploy/ecowebhosting.md).

## Documentation

- [Architecture Decision Records](docs/adr/)
- [Domain model](docs/domain-model.md) *(added in Phase 6)*
