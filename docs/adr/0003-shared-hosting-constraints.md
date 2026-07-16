# ADR 0003 — Shared-hosting (EcoWebHosting) constraints

- **Status**: Accepted
- **Date**: 2026-07-16

## Context

The production target is **EcoWebHosting shared hosting (cPanel)**. Shared
hosting imposes constraints that a VPS/container host would not:

- No root, no Docker.
- No reliable persistent background processes (a 24/7 `queue:work` daemon gets
  killed; there is no systemd/supervisor).
- No headless Chrome/Chromium binary for HTML→PDF rendering.
- MySQL and **Redis are available**; cron jobs are available.
- Node is not guaranteed at runtime.

The local dev machine additionally has no Docker, MySQL, Redis, or `phpredis`.

## Decision

| Concern | Production (EcoWebHosting) | Local dev / CI |
|---|---|---|
| Database | MySQL 8.4 | SQLite (file / `:memory:`) |
| Cache + sessions | Redis (`phpredis`, Predis fallback) | `array` / file |
| Queue | Redis driver, **drained by cron** (`queue:work --stop-when-empty` each minute via `schedule:run`) — not a persistent daemon | `sync` |
| PDF (BOL) | `barryvdh/laravel-dompdf` (pure PHP, no browser) | same |
| Web assets | SPA built locally (`vite build`) and uploaded; no Node on host | Vite dev server |
| Dependencies | `vendor/` uploaded, or Composer run on host if permitted | `composer install` |

Migrations stay **portable** (avoid MySQL-only column types) so SQLite-local and
MySQL-prod do not diverge.

## Consequences

- Redis is a genuine win for cache/sessions and a better queue backend — but it
  does **not** remove the cron, because the constraint is the missing worker
  daemon, not the queue driver.
- dompdf's CSS support is weaker than a real browser; BOL templates use simple
  print CSS. If pixel-perfect multi-page documents are later required, that is
  the trigger to move to a VPS + browser-based renderer.
- If EcoWebHosting turns out to allow a persistent process (e.g. cPanel
  Application Manager), the cron worker can be promoted to a real long-running
  worker with no code change.
- Verify on the host: `phpredis` presence and Redis connection limits.
