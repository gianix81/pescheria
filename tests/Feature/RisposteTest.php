<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Exceptions\InvalidQuantityException;
use App\Exceptions\ResponseWindowClosedException;
use App\Models\Response;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class RisposteTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private ResponseSubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ResponseSubmissionService::class);
    }

    #[Test]
    public function acquisto_calcola_i_kg_dai_colli(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store], ['kg_per_package' => 4.5]);

        $response = $this->service->submitPurchase($opportunity, $store, $cr, 3);

        $this->assertSame(ResponseStatus::INVIATA_ACQUISTO, $response->status);
        $this->assertSame(3, $response->packages);
        $this->assertEqualsWithDelta(13.5, (float) $response->kg, 0.001);
    }

    #[Test]
    public function rifiuto_esplicito_e_diverso_da_mancata_risposta(): void
    {
        [$store, $cr] = $this->storeWithCr();
        [$store2] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store, $store2]);

        $rifiuto = $this->service->submitRefusal($opportunity, $store, $cr, 'Prodotto non adatto');
        $mancante = $this->service->findOrCreate($opportunity, $store2);

        $this->assertSame(ResponseStatus::INVIATA_RIFIUTO, $rifiuto->status);
        $this->assertSame(0, $rifiuto->packages);
        $this->assertNotNull($rifiuto->submitted_at);

        $this->assertSame(ResponseStatus::NON_COMPILATA, $mancante->status);
        $this->assertNull($mancante->submitted_at);

        // Lo zero confermato NON è conteggiato tra i mancanti.
        $stats = $opportunity->completionStats();
        $this->assertSame(1, $stats['inviate']);
        $this->assertSame(1, $stats['mancanti']);
    }

    #[Test]
    public function la_bozza_non_vale_come_invio(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->limitedOpportunity([$store], 10);

        $response = $this->service->saveDraft($opportunity, $store, $cr, 4);

        $this->assertSame(ResponseStatus::BOZZA, $response->status);
        $this->assertSame(0, $response->committed_packages);
        $this->assertSame(0, (int) $opportunity->fresh()->committed_packages);
        $this->assertSame(0, $opportunity->completionStats()['inviate']);
    }

    #[Test]
    public function dopo_la_scadenza_non_si_puo_inviare(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store], [
            'opens_at' => now()->subDay(),
            'closes_at' => now()->subMinute(),
        ]);

        $this->expectException(ResponseWindowClosedException::class);

        $this->service->submitPurchase($opportunity, $store, $cr, 2);
    }

    #[Test]
    public function la_scadenza_non_dipende_dal_client_ma_dallo_stato_del_server(): void
    {
        [$store, $cr] = $this->storeWithCr();
        // Stato APERTA ma finestra già chiusa: il server rifiuta comunque.
        $opportunity = $this->openOpportunity([$store], ['closes_at' => now()->subSecond()]);

        $this->expectException(ResponseWindowClosedException::class);

        $this->service->submitRefusal($opportunity, $store, $cr);
    }

    #[Test]
    public function rispetta_lotto_minimo_e_multiplo(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store], ['min_lot' => 2, 'order_multiple' => 3]);

        // 2, 5, 8 sono validi; 3 e 1 no.
        $this->assertSame(2, $this->service->submitPurchase($opportunity, $store, $cr, 2)->packages);
        $this->assertSame(5, $this->service->submitPurchase($opportunity, $store, $cr, 5)->packages);

        $this->expectException(InvalidQuantityException::class);
        $this->service->submitPurchase($opportunity, $store, $cr, 3);
    }

    #[Test]
    public function quantita_sotto_il_lotto_minimo_e_rifiutata(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store], ['min_lot' => 3]);

        $this->expectException(InvalidQuantityException::class);
        $this->service->submitPurchase($opportunity, $store, $cr, 2);
    }

    #[Test]
    public function acquistare_zero_colli_non_e_ammesso(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store]);

        $this->expectException(InvalidQuantityException::class);
        $this->service->submitPurchase($opportunity, $store, $cr, 0);
    }

    #[Test]
    public function ogni_modifica_registra_valore_precedente_e_nuovo(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store]);

        $response = $this->service->submitPurchase($opportunity, $store, $cr, 2);
        $this->service->submitPurchase($opportunity, $store, $cr, 5);

        $revisions = $response->fresh()->revisions()->orderBy('id')->get();

        $this->assertCount(2, $revisions);
        $this->assertSame(0, $revisions[0]->from_packages);
        $this->assertSame(2, $revisions[0]->to_packages);
        $this->assertSame(2, $revisions[1]->from_packages);
        $this->assertSame(5, $revisions[1]->to_packages);
        $this->assertSame($cr->id, $revisions[1]->user_id);
    }

    #[Test]
    public function disponibilita_aperta_non_ha_limite(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$storeA, $storeB]);

        $this->service->submitPurchase($opportunity, $storeA, $crA, 500);
        $this->service->submitPurchase($opportunity, $storeB, $crB, 999);

        $this->assertSame(1499, $opportunity->fresh()->totalPackagesOrdered());
        $this->assertNull($opportunity->fresh()->remainingPackages());
    }

    #[Test]
    public function il_tecnico_puo_riaprire_una_risposta_dopo_la_scadenza(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $tecnico = $this->tecnico();
        $opportunity = $this->openOpportunity([$store]);

        $response = $this->service->submitPurchase($opportunity, $store, $cr, 2);

        // La finestra si chiude.
        $opportunity->forceFill(['closes_at' => now()->subMinute()])->save();

        $this->service->reopen($response, $tecnico, now()->addHours(2), 'Errore di battitura segnalato dal PdV');

        $aggiornata = $this->service->submitPurchase($opportunity->fresh(), $store, $cr, 6);

        $this->assertSame(6, $aggiornata->packages);
        $this->assertDatabaseHas('audit_logs', ['action' => 'response.reopened']);
    }

    #[Test]
    public function il_buyer_puo_correggere_una_risposta_dopo_la_scadenza_con_motivazione(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $buyer = $this->buyer();
        $opportunity = $this->openOpportunity([$store]);

        $response = $this->service->submitPurchase($opportunity, $store, $cr, 4);
        $opportunity->forceFill(['closes_at' => now()->subMinute()])->save();

        $corretta = $this->service->overrideAfterDeadline($response, $buyer, 2, 'Accordo telefonico con il PdV');

        $this->assertSame(2, $corretta->packages);
        $this->assertDatabaseHas('audit_logs', ['action' => 'response.override']);
        $this->assertDatabaseHas('response_revisions', [
            'response_id' => $response->id,
            'action' => 'CORREZIONE_BUYER',
            'from_packages' => 4,
            'to_packages' => 2,
        ]);
    }

    #[Test]
    public function una_sola_risposta_corrente_per_opportunita_e_punto_vendita(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunity = $this->openOpportunity([$store]);

        $this->service->submitPurchase($opportunity, $store, $cr, 1);
        $this->service->submitRefusal($opportunity, $store, $cr, 'Ripensamento');

        $this->assertSame(1, Response::where('opportunity_id', $opportunity->id)->where('store_id', $store->id)->count());
    }
}
