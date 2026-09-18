<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Livewire\Cr\Dashboard;
use App\Livewire\Cr\Scheda;
use App\Models\Response;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/** Il flusso prioritario: dalla card alla conferma in pochi tocchi. */
class FlussoCapoRepartoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function il_cr_ordina_con_un_pulsante_rapido_e_riceve_la_ricevuta(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['kg_per_package' => 4.0]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('scegliColli', 3)
            ->assertSet('decisione', 'acquista')
            ->call('apriConferma')
            ->assertSet('confermaAperta', true)
            ->call('invia')
            ->assertSet('confermaAperta', false)
            ->assertSee('Ordine registrato')
            ->assertSee('12,00 kg');

        $risposta = Response::firstWhere('store_id', $store->id);
        $this->assertSame(ResponseStatus::INVIATA_ACQUISTO, $risposta->status);
        $this->assertSame(3, $risposta->packages);
    }

    #[Test]
    public function il_pulsante_zero_apre_il_rifiuto_esplicito(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('scegliColli', 0)
            ->assertSet('decisione', 'non_acquista')
            ->set('motivazione', 'Assortimento già coperto')
            ->call('apriConferma')
            ->call('invia')
            ->assertSee('Rifiuto registrato');

        $risposta = Response::firstWhere('store_id', $store->id);
        $this->assertSame(ResponseStatus::INVIATA_RIFIUTO, $risposta->status);
        $this->assertSame(0, $risposta->packages);
        $this->assertSame('Assortimento già coperto', $risposta->refusal_reason);
    }

    #[Test]
    public function senza_decisione_linvio_e_bloccato(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('apriConferma')
            ->assertHasErrors('decisione')
            ->assertSet('confermaAperta', false);

        $this->assertDatabaseCount('responses', 0);
    }

    #[Test]
    public function lo_stepper_rispetta_il_multiplo_di_ordinazione(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['min_lot' => 2, 'order_multiple' => 2]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('scegliColli', 2)
            ->call('incrementa')
            ->assertSet('colli', 4)
            ->call('decrementa')
            ->assertSet('colli', 2)
            ->call('decrementa')
            ->assertSet('colli', 2);     // non scende sotto il lotto minimo
    }

    #[Test]
    public function una_quantita_non_valida_mostra_un_messaggio_comprensibile(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['min_lot' => 5, 'order_multiple' => 5]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->set('decisione', 'acquista')
            ->set('colli', 3)
            ->call('apriConferma')
            ->call('invia')
            ->assertHasErrors('invio')
            ->assertSee('lotto minimo');
    }

    #[Test]
    public function dopo_la_scadenza_linterfaccia_non_consente_linvio(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->subMinute()]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->assertSee('Risposte chiuse')
            ->set('decisione', 'acquista')
            ->set('colli', 2)
            ->call('apriConferma')
            ->call('invia')
            ->assertHasErrors('invio');

        $this->assertDatabaseMissing('responses', ['status' => ResponseStatus::INVIATA_ACQUISTO->value]);
    }

    #[Test]
    public function la_bozza_si_salva_e_puo_diventare_invio(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('scegliColli', 2)
            ->call('salvaBozza');

        $this->assertSame(ResponseStatus::BOZZA, Response::firstWhere('store_id', $store->id)->status);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita->fresh()])
            ->assertSet('colli', 2)
            ->call('apriConferma')
            ->call('invia');

        $this->assertSame(ResponseStatus::INVIATA_ACQUISTO, Response::firstWhere('store_id', $store->id)->status);
    }

    #[Test]
    public function la_dashboard_separa_da_rispondere_bozze_e_inviate(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $daRispondere = $this->openOpportunity([$store], ['title' => 'Da rispondere ora']);
        $inviata = $this->openOpportunity([$store], ['title' => 'Gia inviata']);

        app(ResponseSubmissionService::class)->submitPurchase($inviata, $store, $cr, 1);

        Livewire::actingAs($cr)
            ->test(Dashboard::class)
            ->assertSee('Da rispondere ora')
            ->assertDontSee('Gia inviata')
            ->call('aggiornaVista', 'inviate')
            ->assertSee('Gia inviata')
            ->assertDontSee('Da rispondere ora');
    }

    #[Test]
    public function il_cr_non_puo_aprire_la_scheda_di_un_altro_punto_vendita(): void
    {
        [$storeA, $crA] = $this->storeWithCr('PV501');
        [$storeB] = $this->storeWithCr('PV502');
        $opportunita = $this->openOpportunity([$storeB]);

        Livewire::actingAs($crA)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->assertForbidden();
    }
}
