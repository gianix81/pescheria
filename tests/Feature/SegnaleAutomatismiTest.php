<?php

namespace Tests\Feature;

use App\Support\Sistema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Gli automatismi girano su un cron esterno: se quel cron si ferma, nessuno se
 * ne accorge finché un'opportunità non resta chiusa. L'health check pubblico
 * deve quindi dire da quanto tempo non girano, senza rivelare altro.
 */
class SegnaleAutomatismiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function se_non_sono_mai_girati_lo_dichiara(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertJsonPath('automatismi.attivi', false)
            ->assertJsonPath('automatismi.secondi_dall_ultima', null);
    }

    #[Test]
    public function un_battito_recente_li_dichiara_attivi(): void
    {
        Sistema::registraEsecuzioneScheduler();

        $this->get('/up')
            ->assertOk()
            ->assertJsonPath('automatismi.attivi', true);
    }

    #[Test]
    public function un_battito_vecchio_segnala_che_il_cron_si_e_fermato(): void
    {
        $this->travelTo(now()->subHour(), fn () => Sistema::registraEsecuzioneScheduler());

        $risposta = $this->get('/up')->assertOk();

        $risposta->assertJsonPath('automatismi.attivi', false);
        $this->assertEqualsWithDelta(3600, $risposta->json('automatismi.secondi_dall_ultima'), 5);
    }

    #[Test]
    public function la_versione_resta_esposta(): void
    {
        $this->get('/up')->assertOk()->assertJsonStructure(['stato', 'versione', 'ambiente']);
    }
}
