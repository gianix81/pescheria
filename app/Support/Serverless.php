<?php

namespace App\Support;

/**
 * Preparazione dell'ambiente su piattaforme serverless (Vercel).
 *
 * Il filesystem del progetto è in sola lettura e le variabili d'ambiente vanno
 * inserite a mano nel pannello: dimenticarne una produce un 500 con corpo vuoto,
 * impossibile da diagnosticare. Qui si impostano i valori che possono avere un
 * predefinito sicuro e si elencano quelli che invece l'utente deve fornire.
 */
final class Serverless
{
    /** Variabili senza le quali l'applicazione non può funzionare. */
    public const RICHIESTE = [
        'APP_KEY' => 'chiave di cifratura: generala con «php artisan key:generate --show»',
        'DB_HOST' => 'host del MySQL gestito',
        'DB_DATABASE' => 'nome del database',
        'DB_USERNAME' => 'utente del database',
    ];

    /**
     * Imposta i valori che su serverless hanno un solo significato sensato,
     * senza mai sovrascrivere quelli già definiti dall'utente.
     *
     * @return array<string, string> le variabili effettivamente impostate qui
     */
    public static function preparaAmbiente(string $storage = '/tmp/storage'): array
    {
        $predefiniti = [
            // Il filesystem del progetto è in sola lettura: storage/ va in /tmp.
            'APP_STORAGE_PATH' => $storage,
            'VIEW_COMPILED_PATH' => $storage.'/framework/views',
            // Nessun file di log scrivibile: si scrive sullo stream.
            'LOG_CHANNEL' => 'stderr',
            'LOG_STACK' => 'stderr',
            // /tmp non è condiviso fra invocazioni: sessioni e cache sul database.
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            // Nessun worker persistente.
            'QUEUE_CONNECTION' => 'sync',
        ];

        $impostate = [];

        foreach ($predefiniti as $chiave => $valore) {
            if (self::valore($chiave) !== null) {
                continue;       // scelta esplicita dell'utente: si rispetta
            }

            putenv("{$chiave}={$valore}");
            $_ENV[$chiave] = $valore;
            $_SERVER[$chiave] = $valore;
            $impostate[$chiave] = $valore;
        }

        self::creaDirectory($storage);

        return $impostate;
    }

    /** @return array<string, string> nome => spiegazione, per le variabili mancanti */
    public static function variabiliMancanti(): array
    {
        return array_filter(
            self::RICHIESTE,
            fn (string $chiave) => self::valore($chiave) === null,
            ARRAY_FILTER_USE_KEY,
        );
    }

    public static function dipendenzeInstallate(string $radice): bool
    {
        return is_file($radice.'/vendor/autoload.php');
    }

    public static function creaDirectory(string $storage): void
    {
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
    }

    /** Valore non vuoto della variabile, oppure null. */
    private static function valore(string $chiave): ?string
    {
        foreach ([$_ENV[$chiave] ?? null, $_SERVER[$chiave] ?? null, getenv($chiave)] as $candidato) {
            if (is_string($candidato) && $candidato !== '') {
                return $candidato;
            }
        }

        return null;
    }
}
