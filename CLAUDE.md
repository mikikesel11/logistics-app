# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A freight brokerage operating system (TMS + CRM), currently delivering the MVP:
CRM (customers/carriers) + Loads + Bill of Lading generation. See
`docs/domain-model.md` for the full aggregate list and `docs/adr/` for the
three foundational decisions (API-first split, multi-tenant-ready schema,
shared-hosting constraints) — read those before making architectural changes,
since most non-obvious structural choices trace back to one of them.

Two independent projects in one repo:
- **`api/`** — Laravel 13 (PHP 8.4) JSON API. Source of truth for all business logic.
- **`web/`** — React 19 + TypeScript SPA (Vite) that consumes the API. Native mobile clients will consume the same API later.

## Commands

### API (`api/`)

```bash
cd api
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed        # seeds admin@example.com / password
php artisan serve                 # http://localhost:8000
php artisan test                  # Pest — full suite
php artisan test --filter=LoadCrudTest   # single file/test
```

Do **not** use `composer dev` in a non-interactive/agent shell — it shells out
to `php artisan pail`, which breaks piped/captured output. Use `php artisan serve`
directly instead.

### Web (`web/`)

```bash
cd web
npm install
npm run dev          # http://localhost:5173, proxies /api -> :8000 (VITE_API_PROXY overrides)
npm run build         # tsc -b + vite build -> dist/
npm run lint
npm run typecheck
npm run test:e2e      # Playwright; starts its own dev server, needs a fresh seeded API on :8000
```

Running the full stack locally needs no Docker/MySQL/Redis — SQLite +
array/sync drivers are the default. Docker (`docker compose up -d`) is only
for optional MySQL+Redis parity with production. Full walkthrough, seeded
credentials, and troubleshooting: `docs/deploy/local.md`.

### CI

`.github/workflows/ci.yml` runs three jobs on push/PR to `main`: `api` (Pest on
SQLite), `web` (lint + build), `e2e` (boots the Laravel API, then runs
Playwright against it). Coverage gating is intentionally deferred (see TODO in
the workflow) until a coverage driver is pinned and both suites exist together.

## Architecture

### Backend structure (`api/`)

Business logic lives in `app/Domain/<Area>/`, organized by domain rather than
by technical layer — not standard Laravel MVC-only layout:

- `app/Domain/Crm/` — Customer/Carrier repositories and Data objects
- `app/Domain/Loads/` — `LoadService` (create/update/status transitions), `LoadStatus` enum
- `app/Domain/Billing/` — `BillOfLadingService`, PDF generation job
- `app/Domain/LoadBoard/` — `LoadBoardProvider` interface + `InternalLoadBoardProvider`
- `app/Domain/Comms/` — interface only, unimplemented (future pillar)
- `app/Support/Tenancy/` — multi-tenancy plumbing (see below)
- `app/Support/ApiResponse.php` — the response envelope helper

Controllers (`app/Http/Controllers/Api/`) stay thin: validate via a
`FormRequest`, delegate to a repository/service, wrap the result in
`ApiResponse::success()`/`error()`. Follow the existing controllers
(`CarrierController` is a clean example) for the pattern rather than adding
logic to controllers directly.

**Repository pattern**: interfaces in `app/Domain/*/Repositories/` are bound to
Eloquent implementations in `AppServiceProvider::$bindings`. Add new
repositories/providers there, not with concrete-class type-hints in
constructors.

**Extension seams**: `LoadBoardProvider` and `CommsChannel` are interfaces
designed for a later pillar to implement (e.g. a DAT-backed load board, a
Twilio comms channel) without touching calling code — just add the
implementation and flip the binding in `AppServiceProvider`. See
`docs/domain-model.md` for what each seam is for.

**Response envelope**: every API response is
`{ success, data, error, meta? }` via `App\Support\ApiResponse`. Preserve this
shape for all new endpoints — the web client's `request()`/`requestWithMeta()`
helpers (`web/src/api/client.ts`) assume it and throw `ApiRequestError` when
`success` is false.

### Multi-tenancy (`app/Support/Tenancy/`)

Shared-database, shared-schema tenancy, single tenant seeded for now (ADR
0002) — this is **load-bearing for every new model**, not optional
boilerplate:

- Every tenant-scoped table has an `organization_id` column.
- Models use the `BelongsToOrganization` trait, which registers
  `OrganizationScope` (a global Eloquent scope filtering by
  `organization_id`) and auto-fills `organization_id` on create.
- `TenantContext` resolves the current org from the authenticated user by
  default, but can be explicitly overridden for queued jobs/console commands
  that run outside a request.
- Any new tenant-scoped model **must** use `BelongsToOrganization` and any
  unique constraint on it must be scoped `unique(organization_id, ...)`, not
  global. `tests/Feature/OrganizationIsolationTest.php` is the pattern to
  extend when adding cross-tenant isolation coverage for a new model.

### Load status lifecycle

`App\Domain\Loads\LoadStatus` is an enum encoding the only legal transitions
(`quoted → booked → dispatched → in_transit → delivered → invoiced`, with
`cancelled` reachable from the first three). `LoadService::transitionTo()` is
the only way to change status; illegal transitions throw
`InvalidStatusTransition`, surfaced as HTTP 422. Don't set `status` directly
via mass update outside this path.

### Money

Rates are stored as integer cents (`customer_rate_cents`, `carrier_cost_cents`)
to avoid float drift. `margin_cents` is **derived only**
(`Load::marginCents()`) — never add a persisted margin column.

### Bill of Lading

`BillOfLadingService::generateFromLoad` snapshots the load's parties/freight
at generation time into an immutable `BillOfLading` record — editing the load
afterward must never change an already-issued document. PDF rendering
(`GenerateBillOfLadingPdf`) runs via `barryvdh/laravel-dompdf` (pure PHP, no
headless browser) because the production host has no Chromium available (ADR
0003).

### Testing (`api/`)

Pest, feature-tested against SQLite with `RefreshDatabase`. Use the
`actingAsOrgUser()` helper (`tests/Pest.php`) to create an org + user and
authenticate via Sanctum in one call — this is the standard setup for any
authenticated endpoint test, not a special case.

### Frontend structure (`web/`)

- `src/api/` — `client.ts` (fetch wrapper: bearer-token auth, envelope
  parsing, `ApiRequestError`), `resources.ts` (per-resource calls),
  `types.ts` (hand-mirrored API types — the Laravel API Resources in
  `api/app/Http/Resources` are the source of truth; update both sides
  together when a resource shape changes).
- `src/auth/` — `AuthProvider`/`useAuth`; Sanctum PAT stored in
  `localStorage`, sent as `Authorization: Bearer`. No cookie-based SPA auth
  in this client (unlike ADR 0001's mention of cookie auth as an option).
- `src/components/ui/` — small hand-rolled Tailwind primitives
  (shadcn-style but no shadcn dependency) — check here before adding a UI
  library.
- `src/pages/` — one file per route (Login, Dashboard, Customers, Carriers,
  Loads, LoadDetail).
- `e2e/full-flow.spec.ts` — the single critical-path Playwright spec:
  login → create customer/carrier → create load → walk status lifecycle →
  generate/download BOL PDF. Extend this rather than fragmenting into many
  small E2E specs, per the existing test-plan intent.

### Deployment target

Production is **EcoWebHosting shared hosting (cPanel)** — no root, no
Docker, no persistent daemons, no Chromium (ADR 0003). This is why: queue
jobs are drained by cron (`schedule:run` triggers `queue:work
--stop-when-empty`) rather than a long-running worker, and BOL PDFs render
via dompdf instead of a browser. Keep migrations portable (avoid MySQL-only
column types) since local/CI run SQLite and prod runs MySQL. Full details:
`docs/adr/0003-shared-hosting-constraints.md`, `docs/deploy/ecowebhosting.md`.
