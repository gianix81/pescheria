<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\OpportunityStatus;
use App\Livewire\Tecnico\Monitor;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class MonitorCompilazioniTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function la_matrice_distingue_non_compilato_bozza_acquisto_e_rifiuto(): void
    {
        [$pvAcquisto, $crAcquisto] = $this->storeWithCr('PV601');
        [$pvRifiuto, $crRifiuto] = $this->storeWithCr('PV602');
        [$pvBozza, $crBozza] = $this->storeWithCr('PV603');
        [$pvMancante, $crMancante] = $this->storeWithCr('PV604');

        $opportunita = $this->openOpportunity([$pvAcquisto, $pvRifiuto, $pvBozza, $pvMancante]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $pvAcquisto, $crAcquisto, 3);
        $service->submitRefusal($opportunita->fresh(), $pvRifiuto, $crRifiuto, 'Non serve');
        $service->saveDraft($opportunita->fresh(), $pvBozza, $crBozza, 2);

        $statistiche = $opportunita->fresh()->completionStats();

        $this->assertSame(4, $statistiche['destinatari']);
        $this->assertSame(2, $statistiche['inviate']);       // acquisto + rifiuto
        $this->assertSame(1, $statistiche['acquisti']);
        $this->assertSame(1, $statistiche['rifiuti']);
        $this->assertSame(1, $statistiche['bozze']);
        $this->assertSame(2, $statistiche['mancanti']);      // bozza e non compilato
        $this->assertSame(50.0, $statistiche['percentuale']);

        Livewire::actingAs($this->tecnico())
            ->test(Monitor::class)
            ->assertSee('PV601')
            ->assertSee('PV604')
            ->assertSee('Acquisto inviato')
            ->assertSee('Rifiuto inviato')
            ->assertSee('Non compilata');
    }

    #[Test]
    public function i_filtri_del_monitor_restringono_lelenco(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $aperta = $this->openOpportunity([$store], [
            'article_code' => 'ARTAPERTA',
            'description' => 'Merluzzo ancora aperto',
            'delivery_date' => now()->addDays(2)->toDateString(),
        ]);
        $scaduta = $this->openOpportunity([$store], [
            'article_code' => 'ARTSCADUTA',
            'description' => 'Merluzzo gia scaduto',
            'status' => OpportunityStatus::SCADUTA,
            'closes_at' => now()->subDay(),
            'delivery_date' => now()->addDays(9)->toDateString(),
        ]);

        $componente = Livewire::actingAs($this->tecnico())->test(Monitor::class);

        $componente->set('stato', 'APERTA')
            ->assertSee('Merluzzo ancora aperto')
            ->assertDontSee('Merluzzo gia scaduto');

        $componente->set('stato', 'SCADUTA')
            ->assertSee('Merluzzo gia scaduto')
            ->assertDontSee('Merluzzo ancora aperto');

        $componente->set('stato', '')
            ->set('ricerca', 'Merluzzo gia scaduto')
            ->assertSee('Merluzzo gia scaduto')
            ->assertDontSee('Merluzzo ancora aperto');

        $componente->set('ricerca', '')
            ->set('consegna', $scaduta->delivery_date->toDateString())
            ->assertSee('Merluzzo gia scaduto')
            ->assertDontSee('Merluzzo ancora aperto');
    }

    #[Test]
    public function la_selezione_multipla_dei_mancanti_invia_i_solleciti(): void
    {
        [$pvRisposto, $crRisposto] = $this->storeWithCr('PV611');
        [$pvMancante1, $crMancante1] = $this->storeWithCr('PV612');
        [$pvMancante2, $crMancante2] = $this->storeWithCr('PV613');

        $opportunita = $this->openOpportunity([$pvRisposto, $pvMancante1, $pvMancante2]);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $pvRisposto, $crRisposto, 1);

        Livewire::actingAs($this->tecnico())
            ->test(Monitor::class)
            ->call('selezionaTuttiMancanti', $opportunita->id)
            ->assertSet("selezione.{$opportunita->id}", [$pvMancante1->id, $pvMancante2->id])
            ->call('sollecitaSelezionati', $opportunita->id);

        foreach ([$crMancante1, $crMancante2] as $utente) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $utente->id,
                'type' => NotificationType::SOLLECITO_RISPOSTA->value,
            ]);
        }

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $crRisposto->id,
            'type' => NotificationType::SOLLECITO_RISPOSTA->value,
        ]);
    }

    #[Test]
    public function un_capo_reparto_non_accede_al_monitor(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get(route('tecnico.monitor'))->assertForbidden();
    }
}
