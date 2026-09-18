<?php

/*
|--------------------------------------------------------------------------
| Punto di ingresso per Vercel
|--------------------------------------------------------------------------
| Su Vercel il filesystem è in sola lettura tranne /tmp. Qui prepariamo le
| directory scrivibili PRIMA di avviare Laravel, poi deleghiamo al front
| controller standard. In qualsiasi altro ambiente questo file non viene usato.
*/

$storage = getenv('APP_STORAGE_PATH') ?: '/tmp/storage';

foreach ([
    $storage.'/app/private',
    $storage.'/app/public',
    $storage.'/framework/cache/data',
    $storage.'/framework/sessions',
    $storage.'/framework/views',
    $storage.'/logs',
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

require __DIR__.'/../public/index.php';
