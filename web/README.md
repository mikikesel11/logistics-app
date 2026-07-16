# Freight OS — Web SPA

Responsive React + TypeScript single-page app for the freight brokerage
operating system. Consumes the Laravel API in [`../api`](../api) and exercises
the full **Customer → Carrier → Load → Bill of Lading** flow.

## Stack

- **React 19 + TypeScript** on **Vite 7**
- **Tailwind CSS 4** (via `@tailwindcss/vite`) for styling
- **React Router 7** for routing
- **Playwright** for end-to-end tests
- Bearer-token auth against **Laravel Sanctum** (token persisted in
  `localStorage`; sent as `Authorization: Bearer …`)

No component library is pulled in — a small set of accessible, Tailwind-styled
primitives lives in [`src/components/ui`](src/components/ui) (shadcn-style, but
hand-rolled to stay dependency-light).

## Project layout

```
web/
  src/
    api/          # Typed API client + resource modules + response types
    auth/         # Auth context, provider, useAuth hook
    components/   # App shell, shared widgets, ui/ primitives
    lib/          # useAsync hook + formatting utils
    pages/        # Login, Dashboard, Customers, Carriers, Loads, LoadDetail
  e2e/            # Playwright specs
```

The API contract is mirrored by hand in [`src/api/types.ts`](src/api/types.ts);
the Laravel resources in `../api/app/Http/Resources` are the source of truth.

## Prerequisites

- Node 20.19+ / 22.12+ (Vite 7 requirement)
- A running API — see [`../api/README`](../README.md). Locally it runs on
  SQLite with a seeded admin: **admin@example.com / password**.

## Develop

```bash
npm install
cp .env.example .env       # optional; defaults work for local dev
npm run dev                # http://localhost:5173
```

In dev, requests to `/api/*` are **proxied** to the Laravel server (default
`http://localhost:8000`, override with `VITE_API_PROXY`). This keeps the SPA and
API on one origin — the same shape as production, where the built assets are
served from Laravel's `public/`.

Run the API alongside it:

```bash
cd ../api
php artisan migrate:fresh --seed
php artisan serve            # http://localhost:8000
```

## Scripts

| Script | Purpose |
|---|---|
| `npm run dev` | Vite dev server with `/api` proxy |
| `npm run build` | Type-check (`tsc -b`) + production build to `dist/` |
| `npm run preview` | Serve the production build locally |
| `npm run lint` | ESLint |
| `npm run typecheck` | Type-check without emitting |
| `npm run test:e2e` | Playwright E2E (starts the dev server itself) |

## End-to-end tests

The single critical-flow spec ([`e2e/full-flow.spec.ts`](e2e/full-flow.spec.ts))
logs in, creates a customer and carrier, creates a load linking them, moves it
through its status lifecycle, and generates + downloads a BOL PDF.

```bash
# 1) Fresh, seeded API (in ../api):
php artisan migrate:fresh --seed && php artisan serve

# 2) Run the E2E suite (starts Vite on :5173 automatically):
npx playwright install chromium   # first run only
npm run test:e2e
```

## Production build & deploy

`npm run build` emits static assets to `dist/`. For the EcoWebHosting shared
host, upload `dist/` into the location Laravel serves as `public/` (or a
subdomain) — no Node runtime is needed on the host. Because the SPA calls
`/api/*` on its own origin, it needs no CORS configuration when served from the
same domain as the API. See [`../docs/deploy/ecowebhosting.md`](../docs/deploy/ecowebhosting.md).
