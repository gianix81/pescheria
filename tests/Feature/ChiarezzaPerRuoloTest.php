<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Ogni ruolo, aprendo la propria pagina, deve capire in una riga che cosa sta
 * guardando e qual è la prossima cosa da fare. La frase cambia con la
 * situazione: prima ciò che blocca, poi ciò che aspetta.
 */
class ChiarezzaPerRuoloTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function il_buyer_vede_per_prima_cosa_quello_che_e_stato_respinto(): void
    {
        $buyer = $this->buyer();
        Opportunity::factory()->create(['created_by' => $buyer->id, 'status' => OpportunityStatus::DA_CORREGGERE]);
        Opportunity::factory()->create(['created_by' => $buyer->id, 'status' => OpportunityStatus::BOZZA]);

        $this->actingAs($buyer)
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('respinta dal Tecnico')
            ->assertSee('Nuova');
    }

    #[Test]
    public function il_buyer_senza_nulla_da_correggere_vede_le_aperte(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $this->openOpportunity([$store], ['created_by' => $buyer->id, 'closes_at' => now()->addDays(2)]);

        $this->actingAs($buyer)
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('stanno arrivando le risposte');
    }

    #[Test]
    public function il_buyer_senza_niente_in_corso_e_invitato_a_pubblicare(): void
    {
        $this->actingAs($this->buyer())
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('pubblicane una nuova');
    }

    #[Test]
    public function il_tecnico_vede_per_prima_cosa_cosa_sta_tenendo_ferma(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        $this->actingAs($this->tecnico())
            ->get(route('tecnico.dashboard'))
            ->assertOk()
            ->assertSee('aspetta la tua verifica')
            ->assertSee('i reparti non la vedono')
            ->assertSee(route('tecnico.verifica', $opportunita), escape: false);
    }

    #[Test]
    public function il_tecnico_senza_verifiche_vede_chi_manca(): void
    {
        [$a, $crA] = $this->storeWithCr('PV601');
        [$b, $crB] = $this->storeWithCr('PV602');
        $opportunita = $this->openOpportunity([$a, $b]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $a, $crA, 1);

        $this->actingAs($this->tecnico())
            ->get(route('tecnico.dashboard'))
            ->assertOk()
            ->assertSee('non ha ancora risposto')
            ->assertSee('Sollecita');
    }

    #[Test]
    public function il_tecnico_a_posto_lo_legge_subito(): void
    {
        $this->actingAs($this->tecnico())
            ->get(route('tecnico.dashboard'))
            ->assertOk()
            ->assertSee('Tutto in ordine');
    }

    #[Test]
    public function il_capo_reparto_legge_quante_ne_aspettano_una_risposta(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $this->openOpportunity([$store]);
        $this->openOpportunity([$store]);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertSee('2 opportunità aspettano la tua risposta');
    }

    #[Test]
    public function le_tabelle_larghe_hanno_una_versione_per_telefono(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        // La tabella resta per lo schermo grande, ma esiste anche la lista
        // compatta: su telefono non si scorre di lato.
        foreach ([route('buyer.dashboard'), route('opportunita.index'), route('tecnico.dashboard')] as $url) {
            $risposta = $this->actingAs($this->admin())->get($url)->assertOk();

            $contenuto = $risposta->getContent();
            $this->assertStringContainsString('lg:hidden', $contenuto, "Manca la lista per telefono su {$url}");
            $this->assertStringContainsString('hidden', $contenuto);
        }
    }
}
