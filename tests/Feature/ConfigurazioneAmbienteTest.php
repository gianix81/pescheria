<?php

namespace Tests\Feature;

use Illuminate\Support\Env;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * I pannelli di hosting permettono di creare una variabile d'ambiente lasciando
 * il valore in bianco. In quel caso env('CHIAVE', 'default') restituisce '' e
 * NON il default: con un fuso orario vuoto Laravel non si avvia nemmeno.
 *
 * Questi test bloccano la regressione sulle variabili critiche.
 */
class ConfigurazioneAmbienteTest extends TestCase
{
    /** @var array<string, string|null> */
    private array $valoriOriginali = [];

    private function conVariabileVuota(string $chiave): void
    {
        $this->valoriOriginali[$chiave] = Env::get($chiave);

        Env::getRepository()->set($chiave, '');
    }

    protected function tearDown(): void
    {
        foreach ($this->valoriOriginali as $chiave => $valore) {
            if ($valore === null) {
                Env::getRepository()->clear($chiave);
            } else {
                Env::getRepository()->set($chiave, (string) $valore);
            }
        }

        parent::tearDown();
    }

    /** Ricarica il file di configurazione così com'è, senza cache. */
    private function rileggi(string $file): array
    {
        return require config_path($file.'.php');
    }

    #[Test]
    public function un_fuso_orario_vuoto_ricade_su_utc(): void
    {
        $this->conVariabileVuota('APP_TIMEZONE');

        $config = $this->rileggi('app');

        $this->assertSame('UTC', $config['timezone']);
        $this->assertContains($config['timezone'], timezone_identifiers_list());
    }

    #[Test]
    public function un_fuso_di_visualizzazione_vuoto_ricade_su_roma(): void
    {
        $this->conVariabileVuota('APP_DISPLAY_TIMEZONE');

        $config = $this->rileggi('app');

        $this->assertSame('Europe/Rome', $config['display_timezone']);
        $this->assertContains($config['display_timezone'], timezone_identifiers_list());
    }

    #[Test]
    #[DataProvider('variabiliCritiche')]
    public function le_variabili_critiche_non_restano_mai_vuote(string $chiave, string $file, string $percorso): void
    {
        $this->conVariabileVuota($chiave);

        $valore = data_get($this->rileggi($file), $percorso);

        $this->assertNotSame('', $valore, "{$chiave} vuota lascia {$file}.{$percorso} senza valore.");
        $this->assertNotNull($valore, "{$chiave} vuota lascia {$file}.{$percorso} a null.");
    }

    public static function variabiliCritiche(): array
    {
        return [
            'locale' => ['APP_LOCALE', 'app', 'locale'],
            'locale di riserva' => ['APP_FALLBACK_LOCALE', 'app', 'fallback_locale'],
            'nome applicazione' => ['APP_NAME', 'app', 'name'],
            'url' => ['APP_URL', 'app', 'url'],
            'connessione database' => ['DB_CONNECTION', 'database', 'default'],
            'host database' => ['DB_HOST', 'database', 'connections.mysql.host'],
            'driver sessioni' => ['SESSION_DRIVER', 'session', 'driver'],
            'store cache' => ['CACHE_STORE', 'cache', 'default'],
            'connessione code' => ['QUEUE_CONNECTION', 'queue', 'default'],
            'disco predefinito' => ['FILESYSTEM_DISK', 'filesystems', 'default'],
            'canale di log' => ['LOG_CHANNEL', 'logging', 'default'],
            'mailer' => ['MAIL_MAILER', 'mail', 'default'],
            'disco media' => ['MEDIA_DISK', 'pescheria', 'media.disk'],
        ];
    }

    #[Test]
    public function i_fusi_configurati_sono_identificatori_validi(): void
    {
        // Riproduce ciò che fa Laravel all'avvio (LoadConfiguration).
        $this->assertContains(config('app.timezone'), timezone_identifiers_list());
        $this->assertContains(config('app.display_timezone'), timezone_identifiers_list());

        date_default_timezone_set(config('app.timezone'));
        $this->assertSame(config('app.timezone'), date_default_timezone_get());
    }
}
