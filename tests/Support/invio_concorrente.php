<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Opportunity;
use App\Models\Store;
use App\Models\User;
use App\Services\ResponseSubmissionService;
use Illuminate\Contracts\Console\Kernel;

/*
|--------------------------------------------------------------------------
| Invio concorrente (usato dal test di concorrenza)
|--------------------------------------------------------------------------
| Avvia un'applicazione Laravel completa in un processo separato e invia una
| risposta di acquisto. Lanciando N processi contemporaneamente si verifica che
| il lock di riga MySQL impedisca davvero la sovra-allocazione.
|
| Uso: php tests/Support/invio_concorrente.php <opportunity_id> <store_id> <user_id> <colli> <start_ts>
*/

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $opportunityId, $storeId, $userId, $packages, $startTs] = $argv + [null, null, null, null, null, null];

// Barriera: tutti i processi partono nello stesso microsecondo.
if ($startTs) {
    $attesa = (float) $startTs - microtime(true);
    if ($attesa > 0) {
        usleep((int) ($attesa * 1_000_000));
    }
}

try {
    $opportunity = Opportunity::findOrFail((int) $opportunityId);
    $store = Store::findOrFail((int) $storeId);
    $user = User::findOrFail((int) $userId);

    $response = app(ResponseSubmissionService::class)
        ->submitPurchase($opportunity, $store, $user, (int) $packages);

    echo "OK {$response->packages}\n";
    exit(0);
} catch (InsufficientStockException $e) {
    echo "STOCK {$e->getMessage()}\n";
    exit(0);
} catch (Throwable $e) {
    echo 'ERRORE '.$e->getMessage()."\n";
    exit(1);
}
