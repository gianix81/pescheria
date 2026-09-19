<?php

namespace App\Http\Controllers;

use App\Support\Sistema;
use Illuminate\Http\JsonResponse;

/**
 * Health check della piattaforma, con l'identificativo della versione pubblicata.
 *
 * Serve a rispondere a due domande che dall'esterno sono altrimenti indecidibili:
 * «il sito sta girando il codice che ho appena pubblicato, o un build vecchio?»
 * e «i comandi pianificati stanno girando davvero?». Quest'ultima, senza questo
 * endpoint, si legge solo dalla pagina riservata: un servizio di controllo
 * esterno non potrebbe accorgersi che il cron si è fermato.
 *
 * Espone solo il commit (il repository è comunque leggibile), l'ambiente e da
 * quanti secondi gli automatismi non girano: nessun dato di configurazione,
 * nessun conteggio, nessun indirizzo.
 */
class StatoController extends Controller
{
    /** Oltre questa soglia il cron è considerato fermo: gira ogni minuto. */
    private const SOGLIA_SECONDI = 300;

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'stato' => 'ok',
            'versione' => $this->versione(),
            'ambiente' => config('app.env'),
            'automatismi' => $this->automatismi(),
        ]);
    }

    /**
     * @return array{attivi: bool, secondi_dall_ultima: ?int}
     */
    private function automatismi(): array
    {
        // Il battito sta in cache, quindi sul database: se è irraggiungibile
        // l'health check non deve fallire per questo.
        try {
            $ultima = Sistema::ultimaEsecuzioneScheduler();
        } catch (\Throwable) {
            return ['attivi' => false, 'secondi_dall_ultima' => null];
        }

        if ($ultima === null) {
            return ['attivi' => false, 'secondi_dall_ultima' => null];
        }

        $secondi = (int) $ultima->diffInSeconds(now(), absolute: true);

        return [
            'attivi' => $secondi <= self::SOGLIA_SECONDI,
            'secondi_dall_ultima' => $secondi,
        ];
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
