<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Endpoint usato dove non esiste un cron di sistema (serverless).
 * Deve essere inaccessibile senza il segreto e non deve rivelare la propria esistenza.
 */
class CronEndpointTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function senza_segreto_configurato_lendpoint_non_esiste(): void
    {
        config(['pescheria.cron_secret' => '']);

        $this->get('/cron/esegui')->assertNotFound();
        $this->get('/cron/esegui?token=qualsiasi')->assertNotFound();
    }

    #[Test]
    public function un_token_sbagliato_riceve_404_non_403(): void
    {
        config(['pescheria.cron_secret' => 'segreto-vero']);

        // 404 e non 403: non confermiamo nemmeno che la rotta esista.
        $this->get('/cron/esegui?token=sbagliato')->assertNotFound();
        $this->withHeader('Authorization', 'Bearer sbagliato')->get('/cron/esegui')->assertNotFound();
    }

    #[Test]
    public function con_il_token_corretto_esegue_i_comandi_pianificati(): void
    {
        config(['pescheria.cron_secret' => 'segreto-vero']);

        [$store, $cr] = $this->storeWithCr();

        $daAprire = $this->openOpportunity([$store], [
            'status' => OpportunityStatus::PROGRAMMATA,
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHours(3),
        ]);

        $daScadere = $this->openOpportunity([$store], [
            'status' => OpportunityStatus::APERTA,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->subMinute(),
        ]);

        $this->get('/cron/esegui?token=segreto-vero')
            ->assertOk()
            ->assertJsonStructure(['eseguito_alle', 'comandi' => ['opportunita:apri', 'opportunita:scadi', 'opportunita:solleciti']]);

        $this->assertSame(OpportunityStatus::APERTA, $daAprire->fresh()->status);
        $this->assertSame(OpportunityStatus::SCADUTA, $daScadere->fresh()->status);
    }

    #[Test]
    public function accetta_il_bearer_token_usato_dai_cron_di_vercel(): void
    {
        config(['pescheria.cron_secret' => 'segreto-vero']);

        $this->withHeader('Authorization', 'Bearer segreto-vero')
            ->get('/cron/esegui')
            ->assertOk();
    }
}
