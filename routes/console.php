<?php

use App\Console\Commands\ExpireOpportunities;
use App\Console\Commands\OpenScheduledOpportunities;
use App\Console\Commands\SendResponseReminders;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
| I passaggi di stato temporali non dipendono dal browser: girano qui.
| Tutti i comandi sono idempotenti e protetti da withoutOverlapping().
|
| In produzione serve una sola voce di cron:
|   * * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command(OpenScheduledOpportunities::class)->everyMinute()->withoutOverlapping();
Schedule::command(ExpireOpportunities::class)->everyMinute()->withoutOverlapping();
Schedule::command(SendResponseReminders::class)->everyMinute()->withoutOverlapping();

// Pulizia periodica dei job falliti più vecchi di una settimana.
Schedule::command('queue:prune-failed --hours=168')->daily();
