<?php

namespace App\Support;

use App\Models\OpportunityMedia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fotografia dello stato dell'ambiente, condivisa fra la pagina «Stato sistema»
 * e il comando pescheria:stato.
 *
 * Serve a rispondere senza indovinare a tre domande che dall'esterno non hanno
 * risposta: il database è allineato? i file sopravvivono alle pubblicazioni?
 * lo scheduler sta girando?
 */
final class Sistema
{
    private const CHIAVE_SCHEDULER = 'sistema.scheduler.ultima_esecuzione';

    /** Chiamata dai comandi pianificati: lascia traccia che sono girati. */
    public static function registraEsecuzioneScheduler(): void
    {
        Cache::forever(self::CHIAVE_SCHEDULER, now()->toIso8601String());
    }

    public static function ultimaEsecuzioneScheduler(): ?Carbon
    {
        $valore = Cache::get(self::CHIAVE_SCHEDULER);

        return $valore ? Carbon::parse($valore) : null;
    }

    public static function versione(): string
    {
        foreach (['RAILWAY_GIT_COMMIT_SHA', 'VERCEL_GIT_COMMIT_SHA', 'SOURCE_COMMIT'] as $variabile) {
            if ($sha = trim((string) env($variabile))) {
                return substr($sha, 0, 7);
            }
        }

        $head = base_path('.git/HEAD');

        if (is_readable($head) && preg_match('/ref: (.+)/', (string) file_get_contents($head), $m)) {
            $ref = base_path('.git/'.trim($m[1]));

            if (is_readable($ref)) {
                return substr(trim((string) file_get_contents($ref)), 0, 7);
            }
        }

        return 'sconosciuta';
    }

    public static function databaseRaggiungibile(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function migrazioniNonApplicate(): int
    {
        try {
            $migratore = app('migrator');

            return collect($migratore->getMigrationFiles(database_path('migrations')))
                ->keys()
                ->diff($migratore->getRepository()->getRan())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @return array{disco: string, percorso: string, registrati: int, mancanti: int,
     *               persistente: ?bool, descrizione: string}
     */
    public static function media(): array
    {
        $disco = (string) config('pescheria.media.disk');
        $percorso = (string) config("filesystems.disks.{$disco}.root", '—');

        $registrati = OpportunityMedia::count();
        $mancanti = OpportunityMedia::all()->reject->esiste()->count();

        [$persistente, $descrizione] = self::persistenza($disco, $mancanti);

        return compact('disco', 'percorso', 'registrati', 'mancanti', 'persistente', 'descrizione');
    }

    /**
     * L'applicazione lascia un contrassegno per ogni rilascio: ritrovarne uno
     * di un rilascio precedente è la prova che il disco sopravvive alle
     * pubblicazioni. La piattaforma non lo dichiara, quindi si misura.
     *
     * @return array{0: ?bool, 1: string}
     */
    private static function persistenza(string $disco, int $mancanti): array
    {
        $rilascio = (string) (env('RAILWAY_DEPLOYMENT_ID') ?: env('VERCEL_DEPLOYMENT_ID') ?: gethostname() ?: 'locale');
        $contrassegno = '_persistenza/'.substr(md5($rilascio), 0, 12).'.txt';

        try {
            $archivio = Storage::disk($disco);

            $precedenti = collect($archivio->files('_persistenza'))
                ->reject(fn (string $f) => $f === $contrassegno)
                ->count();

            if (! $archivio->exists($contrassegno)) {
                $archivio->put($contrassegno, now()->toIso8601String());
            }

            if ($precedenti > 0) {
                return [true, "sì — ritrovati i contrassegni di {$precedenti} rilasci precedenti"];
            }

            if ($mancanti > 0) {
                return [false, 'no — il disco è stato azzerato e i file caricati sono andati persi'];
            }

            return [null, 'non ancora determinabile — si saprà dopo la prossima pubblicazione'];
        } catch (\Throwable $e) {
            return [null, 'non verificabile ('.$e->getMessage().')'];
        }
    }
}
