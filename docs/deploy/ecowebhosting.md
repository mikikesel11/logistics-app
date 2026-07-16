# Deploying to EcoWebHosting (shared hosting / cPanel)

Production runbook. Rationale for these choices is in
[ADR 0003](../adr/0003-shared-hosting-constraints.md).

## 1. Verify on the host

- [ ] PHP 8.4+ selectable (cPanel → MultiPHP Manager)
- [ ] MySQL database + user created (cPanel → MySQL Databases)
- [ ] Redis available and the **`phpredis`** extension enabled
      (else `composer require predis/predis` and set `REDIS_CLIENT=predis`);
      check the Redis connection limit
- [ ] Cron jobs available (cPanel → Cron Jobs)
- [ ] SSH access? (determines migration + Composer strategy)

## 2. Layout

- Point the API document root at `api/public/` (or a subdomain
  `api.example.com`).
- Serve the built SPA (`web/dist/`) from the web root or a separate subdomain
  `app.example.com`.

## 3. Production `.env` (in `api/`)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=<cpanel_db>
DB_USERNAME=<cpanel_user>
DB_PASSWORD=<cpanel_pass>

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis        # or predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# If the SPA is on a different subdomain, allow it for Sanctum/CORS:
SANCTUM_STATEFUL_DOMAINS=app.example.com
SESSION_DOMAIN=.example.com
```

## 4. Install & migrate

```bash
# Upload code (git or FTP). Then, over SSH if available:
composer install --no-dev --optimize-autoloader   # else upload vendor/
php artisan key:generate         # once
php artisan migrate --force
php artisan db:seed --force      # first deploy only (creates the org + admin)
php artisan storage:link
```

No SSH? Run migrations via cPanel Terminal, or a one-time password-protected
artisan route that you remove afterward.

## 5. Queue worker via cron (no persistent daemon)

The schedule in `routes/console.php` drains the Redis queue in short bursts.
Add **one** cPanel cron entry:

```
* * * * * cd /home/USER/api && php artisan schedule:run >> /dev/null 2>&1
```

This covers Bill of Lading PDF rendering and any future async work. If the host
later allows a persistent process, replace this with a real `queue:work` worker.

## 6. Caches (after every deploy)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Front end

```bash
cd web && npm ci && npm run build   # locally / in CI
# upload web/dist/ to the web root (or app subdomain)
```

Set the SPA's API base URL to the API origin at build time
(`VITE_API_URL=https://api.example.com`).

## Rollback

- Keep the previous release directory; repoint the document root / symlink back.
- Migrations: prefer additive, reversible migrations. Document any destructive
  step and take a DB backup (cPanel → Backup) before running it.
- Clear caches after rollback (`php artisan optimize:clear`).
