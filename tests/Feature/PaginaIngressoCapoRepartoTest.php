<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Livewire\Cr\Opportunita;
use App\Services\ResponseSubmissionService;
use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Chi apre l'applicazione da un reparto vuole vedere subito la merce, non dei
 * contatori: la pagina d'ingresso è l'elenco delle opportunità e i conteggi
 * vivono nelle schede di filtro.
 */
class PaginaIngressoCapoRepartoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function dopo_il_login_si_atterra_sullelenco_delle_opportunita(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get('/')->assertRedirect(route('cr.opportunita.index'));
        $this->assertSame('/cr/opportunita', parse_url(route('cr.opportunita.index'), PHP_URL_PATH));
    }

    #[Test]
    public function il_vecchio_indirizzo_reindirizza_al_nuovo(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get('/cr/dashboard')->assertRedirect('/cr/opportunita');
    }

    #[Test]
    public function la_pagina_mostra_subito_i_dati_della_merce(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 40, [
            'title' => 'Orata fresca del giorno',
            'article_code' => 'ART55001',
            'plu' => '5501',
            'kg_per_package' => 6.0,
            'sale_price_gross' => 12.90,
            'origin' => 'Italia',
        ]);

        $risposta = $this->actingAs($cr)->get(route('cr.opportunita.index'))->assertOk();

        // Tutto ciò che serve a decidere è visibile senza aprire la scheda.
        $risposta->assertSee('Orata fresca del giorno')
            ->assertSee('ART55001')
            ->assertSee('5501')
            ->assertSee('Italia')
            ->assertSee('€ 12,90/kg')
            ->assertSee('6,0 kg')
            ->assertSee('40 colli')
            ->assertSee(Format::date($opportunita->delivery_date))
            ->assertSee('Rispondi');
    }

    #[Test]
    public function non_ci_sono_riquadri_riepilogativi_da_dashboard(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $this->openOpportunity([$store]);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertDontSee('Da completare')      // era il riquadro della vecchia dashboard
            ->assertSee('Da rispondere');          // ora è una scheda di filtro
    }

    #[Test]
    public function i_conteggi_stanno_nelle_schede_di_filtro(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $this->openOpportunity([$store], ['title' => 'Prima']);
        $this->openOpportunity([$store], ['title' => 'Seconda']);
        $inviata = $this->openOpportunity([$store], ['title' => 'Terza']);

        app(ResponseSubmissionService::class)->submitPurchase($inviata, $store, $cr, 1);

        $componente = Livewire::actingAs($cr)->test(Opportunita::class);

        $conteggi = $componente->viewData('conteggi');

        $this->assertSame(2, $conteggi['da_completare']);
        $this->assertSame(1, $conteggi['inviate']);
    }

    #[Test]
    public function la_riga_di_avviso_compare_solo_quando_c_e_qualcosa_da_fare(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertSee('aspetta la tua risposta');

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 2);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertDontSee('aspetta la tua risposta');
    }

    #[Test]
    public function chi_ha_gia_ordinato_vede_la_propria_quantita_nellelenco(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['kg_per_package' => 5.0]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 3);

        Livewire::actingAs($cr)
            ->test(Opportunita::class)
            ->call('aggiornaVista', 'inviate')
            ->assertSee('Hai ordinato 3 colli')
            ->assertSee('Vedi o modifica');
    }

    #[Test]
    public function unopportunita_chiusa_non_compare_in_prima_pagina(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $aperta = $this->openOpportunity([$store], ['title' => 'Ancora aperta']);
        $chiusa = $this->openOpportunity([$store], [
            'title' => 'Gia chiusa',
            'status' => OpportunityStatus::CHIUSA,
            'closed_at' => now()->subHour(),
        ]);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertSee('Ancora aperta')
            ->assertDontSee('Gia chiusa');

        // Ma resta consultabile nello storico.
        Livewire::actingAs($cr)
            ->test(Opportunita::class)
            ->call('aggiornaVista', 'storico')
            ->assertSee('Gia chiusa');
    }

    #[Test]
    public function unopportunita_scaduta_esce_di_scena_anche_se_lo_scheduler_e_fermo(): void
    {
        [$store, $cr] = $this->storeWithCr();

        // Stato ancora APERTA perché il passaggio a SCADUTA non è stato eseguito,
        // ma il termine è passato: il server rifiuterebbe comunque l'invio.
        $scaduta = $this->openOpportunity([$store], [
            'title' => 'Termine passato',
            'closes_at' => now()->subMinutes(5),
        ]);
        $this->assertSame(OpportunityStatus::APERTA, $scaduta->fresh()->status);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertDontSee('Termine passato');

        Livewire::actingAs($cr)
            ->test(Opportunita::class)
            ->call('aggiornaVista', 'storico')
            ->assertSee('Termine passato');
    }

    #[Test]
    public function elenco_e_conteggi_dicono_la_stessa_cosa(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->openOpportunity([$store], ['title' => 'Valida']);
        $this->openOpportunity([$store], ['title' => 'Termine passato', 'closes_at' => now()->subMinute()]);

        $componente = Livewire::actingAs($cr)->test(Opportunita::class);

        // Prima il contatore diceva 1 e la lista ne mostrava 2.
        $this->assertSame(1, $componente->viewData('conteggi')['da_completare']);
        $this->assertCount(1, $componente->viewData('opportunita')->items());
    }

    #[Test]
    public function unopportunita_esaurita_lo_dichiara_nellelenco(): void
    {
        [$mio, $io] = $this->storeWithCr('PV941');
        [$altro, $crAltro] = $this->storeWithCr('PV942');
        $opportunita = $this->limitedOpportunity([$mio, $altro], 5);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $altro, $crAltro, 5);

        $this->actingAs($io)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertSee('Esaurito');
    }
}
