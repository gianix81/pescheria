<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\OpportunityStatus;
use App\Livewire\Cr\Scheda;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Tre momenti in cui si avvisa su WhatsApp: invio in verifica, apertura,
 * conferma di un punto vendita. Verso le persone l'invio può essere automatico;
 * verso un gruppo nessuna API ufficiale lo consente, quindi si prepara un
 * collegamento con il messaggio già scritto.
 */
class AvvisiWhatsAppTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function il_collegamento_apre_whatsapp_con_il_testo_gia_pronto(): void
    {
        $link = WhatsApp::link("Riga uno\nRiga due & tre");

        $this->assertStringStartsWith('https://wa.me/?text=', $link);
        $this->assertStringContainsString('Riga%20uno', $link);
        $this->assertStringContainsString('%26', $link);      // & correttamente codificato
        $this->assertSame("Riga uno\nRiga due & tre", rawurldecode(substr($link, strlen('https://wa.me/?text='))));
    }

    #[Test]
    public function il_messaggio_di_verifica_contiene_riferimento_e_collegamento(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create(['title' => 'Orata del giorno']);
        $opportunita->stores()->sync([$store->id]);

        $testo = WhatsApp::perVerifica($opportunita->fresh());

        $this->assertStringContainsString('Da verificare', $testo);
        $this->assertStringContainsString($opportunita->reference, $testo);
        $this->assertStringContainsString($opportunita->article_code, $testo);
        $this->assertStringContainsString(route('opportunita.show', $opportunita), $testo);
    }

    #[Test]
    public function il_messaggio_di_apertura_porta_al_percorso_del_capo_reparto(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 25, ['title' => 'Cozze di Scardovari']);

        $testo = WhatsApp::perApertura($opportunita->fresh());

        $this->assertStringContainsString('Cozze di Scardovari', $testo);
        $this->assertStringContainsString('25 colli disponibili', $testo);
        $this->assertStringContainsString('Consegna', $testo);
        // Il gruppo è fatto di capi reparto: il collegamento deve portare alla loro scheda.
        $this->assertStringContainsString(route('cr.opportunita.show', $opportunita), $testo);
        $this->assertStringContainsString('solo dall\'app', $testo);
    }

    #[Test]
    public function il_messaggio_di_risposta_riporta_quantita_e_totale(): void
    {
        [$storeA, $crA] = $this->storeWithCr('PV901');
        [$storeB, $crB] = $this->storeWithCr('PV902');
        $opportunita = $this->openOpportunity([$storeA, $storeB], ['kg_per_package' => 5.0]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $storeB, $crB, 2);
        $risposta = $service->submitPurchase($opportunita->fresh(), $storeA, $crA, 3);

        $testo = WhatsApp::perRisposta($risposta->fresh(['opportunity', 'store']));

        $this->assertStringContainsString('PV901 ordina 3 colli', $testo);
        $this->assertStringContainsString('15,0 kg', $testo);
        $this->assertStringContainsString('Totale finora: 5 colli', $testo);
    }

    #[Test]
    public function il_messaggio_di_rifiuto_lo_dice_chiaramente(): void
    {
        [$store, $cr] = $this->storeWithCr('PV911');
        $opportunita = $this->openOpportunity([$store]);

        $risposta = app(ResponseSubmissionService::class)->submitRefusal($opportunita, $store, $cr, 'Coperto');

        $testo = WhatsApp::perRisposta($risposta->fresh(['opportunity', 'store']));

        $this->assertStringContainsString('PV911 non acquista', $testo);
    }

    #[Test]
    public function la_scheda_in_verifica_apre_la_chat_diretta_col_tecnico(): void
    {
        [$store] = $this->storeWithCr();
        $tecnico = $this->tecnico();
        $tecnico->forceFill(['first_name' => 'Giulia', 'phone' => '+39 333 111 2233'])->save();

        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$store->id]);

        $this->actingAs($this->buyer())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('Avvisa i Tecnici su WhatsApp')
            ->assertSee('Scrivi a Giulia')
            // Il numero normalizzato finisce nel collegamento: apre quella conversazione.
            ->assertSee('wa.me/393331112233', escape: false);
    }

    #[Test]
    public function senza_numero_in_anagrafica_si_ripiega_sulla_scelta_della_chat(): void
    {
        [$store] = $this->storeWithCr();
        $tecnico = $this->tecnico();
        $tecnico->forceFill(['phone' => null])->save();

        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$store->id]);

        $this->actingAs($this->buyer())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('Scegli la chat')
            ->assertSee('nessun numero in anagrafica');
    }

    #[Test]
    public function i_punti_vendita_mancanti_si_sollecitano_uno_per_uno(): void
    {
        [$rispondente, $crRispondente] = $this->storeWithCr('PV931');
        [$mancante, $crMancante] = $this->storeWithCr('PV932');
        $crMancante->forceFill(['first_name' => 'Anna', 'phone' => '333 4445566'])->save();

        $opportunita = $this->openOpportunity([$rispondente, $mancante]);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $rispondente, $crRispondente, 2);

        $this->actingAs($this->tecnico())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('Sollecita chi non ha ancora risposto (1)')
            ->assertSee('PV932')
            ->assertSee('Scrivi a Anna')
            ->assertSee('wa.me/393334445566', escape: false);

        // Chi ha già risposto compare nella tabella delle risposte, ma non fra i
        // solleciti: il conteggio nell'intestazione lo dimostra.
        $this->assertSame(
            1,
            $opportunita->fresh()->completionStats()['mancanti'],
        );
    }

    #[Test]
    public function il_numero_di_telefono_viene_normalizzato(): void
    {
        $this->assertSame('393331112233', WhatsApp::numero('+39 333 111 2233'));
        $this->assertSame('393331112233', WhatsApp::numero('0039 333 1112233'));
        $this->assertSame('393331112233', WhatsApp::numero('333 1112233'));
        $this->assertSame('393331112233', WhatsApp::numero('0333 1112233'));
        $this->assertSame('14155550132', WhatsApp::numero('+1 415 555 0132'));
        $this->assertNull(WhatsApp::numero(null));
        $this->assertNull(WhatsApp::numero('   '));
        $this->assertNull(WhatsApp::numero('non un numero'));
        $this->assertNull(WhatsApp::numero('12'));
    }

    #[Test]
    public function con_il_numero_il_collegamento_apre_quella_conversazione(): void
    {
        $conNumero = WhatsApp::link('ciao', '+39 333 111 2233');
        $senzaNumero = WhatsApp::link('ciao');

        $this->assertStringStartsWith('https://wa.me/393331112233?text=', $conNumero);
        // Senza numero WhatsApp chiede a chi inviare: è così che si raggiunge un gruppo.
        $this->assertStringStartsWith('https://wa.me/?text=', $senzaNumero);
    }

    #[Test]
    public function la_scheda_aperta_propone_la_condivisione_nel_gruppo(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->actingAs($this->tecnico())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('Condividi nel gruppo WhatsApp');
    }

    #[Test]
    public function il_capo_reparto_puo_comunicare_il_proprio_ordine_al_gruppo(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->call('scegliColli', 2)
            ->call('apriConferma')
            ->call('invia')
            ->assertSee('Comunica al gruppo');
    }

    #[Test]
    public function quando_un_punto_vendita_risponde_buyer_e_tecnico_lo_sanno(): void
    {
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();
        [$store, $cr] = $this->storeWithCr('PV921');
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 4);

        foreach ([$buyer, $tecnico] as $destinatario) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $destinatario->id,
                'type' => NotificationType::RISPOSTA_INVIATA->value,
                'title' => 'PV921 ordina 4 colli',
            ]);
        }
    }

    #[Test]
    public function linvio_in_verifica_avvisa_i_tecnici_anche_in_app(): void
    {
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();
        [$store] = $this->storeWithCr();

        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id, 'status' => OpportunityStatus::BOZZA]);
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        app(OpportunityWorkflowService::class)->submitForReview($opportunita->fresh(['stores', 'media']), $buyer);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tecnico->id,
            'type' => NotificationType::OPPORTUNITA_IN_VERIFICA->value,
        ]);
    }
}
