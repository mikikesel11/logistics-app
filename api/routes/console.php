<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| On EcoWebHosting shared hosting there is no persistent queue worker daemon.
| A single cPanel cron runs `php artisan schedule:run` every minute (see
| docs/deploy/ecowebhosting.md), and the schedule below drains the queue in
| short bursts. If the host later allows a persistent process, run a real
| `queue:work` worker and remove these entries.
|
*/

Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('queue:prune-batches --hours=48')->daily();
