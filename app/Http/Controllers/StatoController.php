<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Health check della piattaforma, con l'identificativo della versione pubblicata.
 *
 * Serve a rispondere a una domanda che dall'esterno è altrimenti indecidibile:
 * «il sito sta girando il codice che ho appena pubblicato, o un build vecchio?».
 * Espone solo il commit (il repository è comunque leggibile) e l'ambiente:
 * nessun dato di configurazione, nessun conteggio, nessun indirizzo.
 */
class StatoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'stato' => 'ok',
            'versione' => $this->versione(),
            'ambiente' => config('app.env'),
        ]);
    }

    private function versione(): string
    {
        foreach (['RAILWAY_GIT_COMMIT_SHA', 'VERCEL_GIT_COMMIT_SHA', 'SOURCE_COMMIT', 'GIT_COMMIT'] as $variabile) {
            $sha = trim((string) env($variabile));

            if ($sha !== '') {
                return substr($sha, 0, 7);
            }
        }

        // In sviluppo si legge direttamente dal repository.
        $head = base_path('.git/HEAD');

        if (is_readable($head) && preg_match('/ref: (.+)/', (string) file_get_contents($head), $m)) {
            $ref = base_path('.git/'.trim($m[1]));

            if (is_readable($ref)) {
                return substr(trim((string) file_get_contents($ref)), 0, 7);
            }
        }

        return 'sconosciuta';
    }
}
