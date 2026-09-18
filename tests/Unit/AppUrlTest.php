<?php

namespace Tests\Unit;

use App\Support\AppUrl;
use Illuminate\Support\Env;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Un APP_URL non valido non deve fermare il build: `config:cache` costruisce una
 * Request a partire da quel valore e con un URI malformato solleva «Invalid URI».
 */
class AppUrlTest extends TestCase
{
    private array $originali = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['APP_URL', 'RAILWAY_PUBLIC_DOMAIN', 'VERCEL_URL', 'RENDER_EXTERNAL_HOSTNAME'] as $chiave) {
            $this->originali[$chiave] = Env::get($chiave);
            Env::getRepository()->clear($chiave);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originali as $chiave => $valore) {
            $valore === null
                ? Env::getRepository()->clear($chiave)
                : Env::getRepository()->set($chiave, (string) $valore);
        }

        parent::tearDown();
    }

    private function conAmbiente(array $valori): void
    {
        foreach ($valori as $chiave => $valore) {
            Env::getRepository()->set($chiave, $valore);
        }
    }

    #[Test]
    public function un_url_valido_viene_usato_cosi_come_e(): void
    {
        $this->conAmbiente(['APP_URL' => 'https://ordini.pescheria.it']);

        $this->assertSame('https://ordini.pescheria.it', AppUrl::risolvi());
    }

    #[Test]
    public function la_barra_finale_viene_rimossa(): void
    {
        $this->conAmbiente(['APP_URL' => 'https://ordini.pescheria.it/']);

        $this->assertSame('https://ordini.pescheria.it', AppUrl::risolvi());
    }

    #[Test]
    #[DataProvider('urlNonUtilizzabili')]
    public function un_url_non_utilizzabile_non_viene_accettato(string $valore): void
    {
        $this->conAmbiente(['APP_URL' => $valore]);

        $risolto = AppUrl::risolvi();

        $this->assertSame('http://localhost', $risolto);
        // Deve sempre restare costruibile una Request: è ciò che fa config:cache.
        $this->assertNotNull(parse_url($risolto, PHP_URL_HOST));
    }

    public static function urlNonUtilizzabili(): array
    {
        return [
            'riferimento non risolto' => ['https://${{RAILWAY_PUBLIC_DOMAIN}}'],
            'riferimento con spazi' => ['https://${{ MySQL.HOST }}'],
            'solo schema' => ['https://'],
            'vuoto' => [''],
            'solo spazi' => ['   '],
            'senza schema' => ['ordini.pescheria.it'],
        ];
    }

    #[Test]
    public function senza_app_url_si_usa_il_dominio_della_piattaforma(): void
    {
        $this->conAmbiente(['RAILWAY_PUBLIC_DOMAIN' => 'pescheria-production.up.railway.app']);

        $this->assertSame('https://pescheria-production.up.railway.app', AppUrl::risolvi());
    }

    #[Test]
    public function un_riferimento_non_risolto_scarta_anche_il_dominio_della_piattaforma(): void
    {
        $this->conAmbiente([
            'APP_URL' => 'https://${{RAILWAY_PUBLIC_DOMAIN}}',
            'RAILWAY_PUBLIC_DOMAIN' => '${{RAILWAY_PUBLIC_DOMAIN}}',
        ]);

        $this->assertSame('http://localhost', AppUrl::risolvi());
    }

    #[Test]
    public function app_url_esplicito_ha_la_precedenza_sul_dominio_della_piattaforma(): void
    {
        $this->conAmbiente([
            'APP_URL' => 'https://ordini.pescheria.it',
            'RAILWAY_PUBLIC_DOMAIN' => 'pescheria-production.up.railway.app',
        ]);

        $this->assertSame('https://ordini.pescheria.it', AppUrl::risolvi());
    }
}
