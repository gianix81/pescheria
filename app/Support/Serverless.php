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

    /** Variabili che indicano l'intenzione di usare un database vero. */
    private const INDIZI_DATABASE = ['DB_HOST', 'DB_DATABASE', 'DB_URL', 'DB_SOCKET'];

    /**
     * La modalità dimostrativa è attiva?
     *
     * Oltre a DEMO_MODE=true, si attiva da sola quando l'ambiente non contiene
     * NESSUN indizio di un database configurato: in quel caso non esistono dati
     * reali da mettere a rischio, e mostrare l'applicazione funzionante è più
     * utile di una pagina di errore.
     *
     * Basta però una sola variabile DB_* perché la demo non si attivi: se chi
     * configura ha iniziato a impostare un database vero e ha dimenticato
     * qualcosa, deve vedere l'elenco di ciò che manca, non ritrovarsi gli
     * ordini scritti su un SQLite temporaneo.
     */
    public static function inDemo(): bool
    {
        $esplicita = self::valore('DEMO_MODE');

        if ($esplicita !== null) {
            return filter_var($esplicita, FILTER_VALIDATE_BOOL);
        }

        return ! self::databaseConfigurato();
    }

    /** Almeno una variabile di connessione al database è valorizzata? */
    public static function databaseConfigurato(): bool
    {
        foreach (self::INDIZI_DATABASE as $chiave) {
            if (self::valore($chiave) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Configura l'ambiente dimostrativo: database SQLite temporaneo, sessioni
     * nel cookie (le istanze serverless non condividono niente fra loro) e una
     * chiave di cifratura derivata dal deploy, così resta la stessa per tutte
     * le istanze dello stesso rilascio.
     *
     * @return array<string, string>
     */
    public static function preparaDemo(string $database = '/tmp/demo/pescheria.sqlite'): array
    {
        $cartella = dirname($database);

        if (! is_dir($cartella)) {
            @mkdir($cartella, 0755, true);
        }

        $predefiniti = [
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $database,
            'DEMO_DATABASE' => $database,
            // Sessione nel cookie: senza stato condiviso è l'unica che regge
            // il passaggio da un'istanza all'altra.
            'SESSION_DRIVER' => 'cookie',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'APP_KEY' => self::chiaveDimostrativa(),
        ];

        $impostate = [];

        foreach ($predefiniti as $chiave => $valore) {
            if (self::valore($chiave) !== null) {
                continue;
            }

            putenv("{$chiave}={$valore}");
            $_ENV[$chiave] = $valore;
            $_SERVER[$chiave] = $valore;
            $impostate[$chiave] = $valore;
        }

        return $impostate;
    }

    /**
     * Chiave derivata dall'identificativo del rilascio: stabile fra le istanze
     * dello stesso deploy, diversa a ogni nuovo deploy. Accettabile solo in
     * demo, dove i dati sono usa e getta.
     */
    private static function chiaveDimostrativa(): string
    {
        $seme = self::valore('VERCEL_DEPLOYMENT_ID')
            ?? self::valore('VERCEL_GIT_COMMIT_SHA')
            ?? 'dimostrazione-locale';

        return 'base64:'.base64_encode(hash('sha256', 'pescheria|'.$seme, true));
    }

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
        if (self::inDemo()) {
            return [];      // in demo l'applicazione si configura da sola
        }

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
