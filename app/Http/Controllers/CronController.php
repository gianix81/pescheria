<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Esecuzione dei comandi pianificati via HTTP.
 *
 * Serve sulle piattaforme serverless, dove non esiste un processo `schedule:work`
 * né un cron di sistema. L'endpoint è protetto da CRON_SECRET: senza il segreto
 * corretto risponde 404, per non rivelarne nemmeno l'esistenza.
 *
 * Su un server tradizionale questo endpoint non serve: basta il cron di sistema
 * che invoca `php artisan schedule:run` (vedi README).
 */
class CronController extends Controller
{
    /** Comandi eseguiti a ogni chiamata, nell'ordine. Tutti idempotenti. */
    private const COMANDI = [
        'opportunita:apri',
        'opportunita:scadi',
        'opportunita:solleciti',
    ];

    public function run(Request $request): JsonResponse
    {
        abort_unless($this->autorizzato($request), 404);

        $esiti = [];

        foreach (self::COMANDI as $comando) {
            $codice = Artisan::call($comando);

            $esiti[$comando] = [
                'exit_code' => $codice,
                'output' => trim(Artisan::output()),
            ];
        }

        return response()->json([
            'eseguito_alle' => now()->toIso8601String(),
            'comandi' => $esiti,
        ]);
    }

    /**
     * Accetta sia l'header `Authorization: Bearer <segreto>` usato dai cron di
     * Vercel, sia `?token=<segreto>` per i servizi di cron esterni.
     */
    private function autorizzato(Request $request): bool
    {
        $segreto = (string) config('pescheria.cron_secret');

        if ($segreto === '') {
            return false;       // senza segreto configurato l'endpoint resta chiuso
        }

        $fornito = $request->bearerToken() ?: (string) $request->query('token');

        return $fornito !== '' && hash_equals($segreto, $fornito);
    }
}
