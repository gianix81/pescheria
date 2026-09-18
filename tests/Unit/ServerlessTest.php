<?php

namespace Tests\Unit;

use App\Support\Serverless;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Su serverless una variabile dimenticata produce un 500 con corpo vuoto,
 * impossibile da diagnosticare da fuori. Questi test verificano che
 * l'ambiente si prepari da solo dove può, e che dica chiaramente cosa manca
 * dove non può.
 */
class ServerlessTest extends TestCase
{
    private array $ambienteOriginale = [];

    protected function setUp(): void
    {
        parent::setUp();

        $chiavi = array_merge(array_keys(Serverless::RICHIESTE), [
            'APP_STORAGE_PATH', 'VIEW_COMPILED_PATH', 'LOG_CHANNEL',
            'LOG_STACK', 'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION',
        ]);

        foreach ($chiavi as $chiave) {
            $this->ambienteOriginale[$chiave] = getenv($chiave);
            putenv($chiave);
            unset($_ENV[$chiave], $_SERVER[$chiave]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->ambienteOriginale as $chiave => $valore) {
            if ($valore === false) {
                putenv($chiave);
                unset($_ENV[$chiave], $_SERVER[$chiave]);
            } else {
                putenv("{$chiave}={$valore}");
                $_ENV[$chiave] = $valore;
                $_SERVER[$chiave] = $valore;
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function imposta_i_predefiniti_sicuri_per_il_filesystem_in_sola_lettura(): void
    {
        $storage = sys_get_temp_dir().'/pescheria-test-'.uniqid();

        $impostate = Serverless::preparaAmbiente($storage);

        $this->assertSame($storage, $impostate['APP_STORAGE_PATH']);
        $this->assertSame($storage.'/framework/views', $impostate['VIEW_COMPILED_PATH']);
        $this->assertSame('stderr', $impostate['LOG_CHANNEL']);
        $this->assertSame('database', $impostate['SESSION_DRIVER']);
        $this->assertSame('sync', $impostate['QUEUE_CONNECTION']);

        // I valori devono essere visibili a Laravel in tutti i modi in cui li legge.
        $this->assertSame($storage, getenv('APP_STORAGE_PATH'));
        $this->assertSame($storage, $_ENV['APP_STORAGE_PATH']);
        $this->assertSame($storage, $_SERVER['APP_STORAGE_PATH']);
    }

    #[Test]
    public function crea_le_directory_scrivibili(): void
    {
        $storage = sys_get_temp_dir().'/pescheria-test-'.uniqid();

        Serverless::preparaAmbiente($storage);

        foreach (['framework/views', 'framework/sessions', 'framework/cache/data', 'logs', 'app/private'] as $sotto) {
            $this->assertDirectoryExists($storage.'/'.$sotto);
        }
    }

    #[Test]
    public function non_sovrascrive_le_scelte_esplicite(): void
    {
        putenv('LOG_CHANNEL=daily');
        $_ENV['LOG_CHANNEL'] = 'daily';

        $impostate = Serverless::preparaAmbiente(sys_get_temp_dir().'/pescheria-test-'.uniqid());

        $this->assertArrayNotHasKey('LOG_CHANNEL', $impostate);
        $this->assertSame('daily', getenv('LOG_CHANNEL'));
    }

    #[Test]
    public function elenca_le_variabili_che_mancano(): void
    {
        $mancanti = Serverless::variabiliMancanti();

        $this->assertSame(
            ['APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'],
            array_keys($mancanti),
        );

        // L'elenco spiega cosa fare, non si limita al nome.
        $this->assertStringContainsString('key:generate', $mancanti['APP_KEY']);
    }

    #[Test]
    public function una_variabile_presente_ma_vuota_conta_come_mancante(): void
    {
        putenv('APP_KEY=');
        $_ENV['APP_KEY'] = '';

        $this->assertArrayHasKey('APP_KEY', Serverless::variabiliMancanti());
    }

    #[Test]
    public function una_variabile_valorizzata_non_e_mancante(): void
    {
        foreach (array_keys(Serverless::RICHIESTE) as $chiave) {
            putenv("{$chiave}=valore");
            $_ENV[$chiave] = 'valore';
        }

        $this->assertSame([], Serverless::variabiliMancanti());
    }

    #[Test]
    public function rileva_le_dipendenze_php_mancanti(): void
    {
        $this->assertTrue(Serverless::dipendenzeInstallate(dirname(__DIR__, 2)));
        $this->assertFalse(Serverless::dipendenzeInstallate('/percorso/inesistente'));
    }
}
