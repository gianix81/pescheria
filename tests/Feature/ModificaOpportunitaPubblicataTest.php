<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\OpportunityStatus;
use App\Livewire\Buyer\OpportunitaForm;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Models\Response;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Capitolato §3: il Buyer «crea e modifica opportunità in qualsiasi momento».
 * La modifica di un'opportunità già pubblicata non è però mai silenziosa, e non
 * può rendere incoerenti gli ordini già raccolti.
 */
class ModificaOpportunitaPubblicataTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private function form($buyer, Opportunity $opportunita)
    {
        return Livewire::actingAs($buyer)->test(OpportunitaForm::class, ['opportunity' => $opportunita]);
    }

    #[Test]
    public function modificare_unopportunita_aperta_la_rimanda_in_verifica(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id, 'title' => 'Titolo iniziale']);

        $this->form($buyer, $opportunita)
            ->set('title', 'Titolo corretto')
            ->set('commercial_description', 'Descrizione aggiornata')
            ->call('salvaBozza')
            ->assertHasNoErrors();

        $aggiornata = $opportunita->fresh();

        $this->assertSame('Titolo corretto', $aggiornata->title);
        // La modifica non va in linea da sola: la ripubblicazione passa dal Tecnico.
        $this->assertSame(OpportunityStatus::IN_VERIFICA, $aggiornata->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'opportunity.updated_published']);
    }

    #[Test]
    public function finche_e_in_verifica_i_punti_vendita_non_la_vedono(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $this->actingAs($cr)->get(route('cr.opportunita.show', $opportunita))->assertOk();

        $this->form($buyer, $opportunita)->set('title', 'Prezzo rivisto')->call('salvaBozza');

        $this->actingAs($cr)->get(route('cr.opportunita.show', $opportunita->fresh()))->assertForbidden();
    }

    #[Test]
    public function il_tecnico_conferma_e_lopportunita_torna_disponibile(): void
    {
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);
        // Un'opportunità pubblicata ha necessariamente superato il controllo sui media.
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        // Un ordine già raccolto prima della modifica.
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 3);

        $this->form($buyer, $opportunita->fresh())->set('title', 'Titolo rivisto')->call('salvaBozza');
        $this->assertSame(OpportunityStatus::IN_VERIFICA, $opportunita->fresh()->status);

        app(OpportunityWorkflowService::class)
            ->approve($opportunita->fresh(['stores', 'media']), $tecnico, 'Modifica verificata');

        $ripubblicata = $opportunita->fresh();

        $this->assertSame(OpportunityStatus::APERTA, $ripubblicata->status);
        $this->actingAs($cr)->get(route('cr.opportunita.show', $ripubblicata))->assertOk();

        // L'ordine già raccolto resta.
        $this->assertSame(3, (int) Response::first()->packages);

        // E i punti vendita vengono avvisati che qualcosa è cambiato.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $cr->id,
            'type' => NotificationType::OPPORTUNITA_MODIFICATA->value,
        ]);
    }

    #[Test]
    public function la_modifica_avvisa_subito_i_tecnici(): void
    {
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $this->form($buyer, $opportunita)->set('title', 'Da ricontrollare')->call('salvaBozza');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tecnico->id,
            'type' => NotificationType::OPPORTUNITA_IN_VERIFICA->value,
            'title' => 'Da ripubblicare: Da ricontrollare',
        ]);
    }

    #[Test]
    public function la_schermata_di_verifica_segnala_la_ripubblicazione(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 2);
        $this->form($buyer, $opportunita->fresh())->set('title', 'Rivista')->call('salvaBozza');

        $this->actingAs($this->tecnico())
            ->get(route('tecnico.verifica', $opportunita->fresh()))
            ->assertOk()
            ->assertSee('Ripubblicazione')
            ->assertSee('Ha già raccolto 1 risposte');
    }

    #[Test]
    public function la_modifica_avvisa_i_punti_vendita(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $this->form($buyer, $opportunita)->set('title', 'Prezzo rivisto')->call('salvaBozza');

        // Sanno subito che è ferma, senza scoprirlo trovandola sparita.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $cr->id,
            'type' => NotificationType::OPPORTUNITA_MODIFICATA->value,
            'title' => 'In aggiornamento: Prezzo rivisto',
        ]);
    }

    #[Test]
    public function modificare_i_kg_per_collo_riallinea_gli_ordini_gia_raccolti(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id, 'kg_per_package' => 5.0]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 4);
        $this->assertEqualsWithDelta(20.0, (float) Response::first()->kg, 0.001);

        $this->form($buyer, $opportunita->fresh())
            ->set('kg_per_package', 6.5)
            ->call('salvaBozza')
            ->assertHasNoErrors();

        // 4 colli × 6,5 kg: senza il riallineamento resterebbero 20 kg.
        $this->assertEqualsWithDelta(26.0, (float) Response::first()->kg, 0.001);
        $this->assertDatabaseHas('audit_logs', ['action' => 'opportunity.kg_recalculated']);
    }

    #[Test]
    public function non_si_scende_sotto_i_colli_gia_confermati(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 20, ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 12);

        $this->form($buyer, $opportunita->fresh())
            ->set('total_packages', 5)
            ->call('salvaBozza')
            ->assertHasErrors('salvataggio')
            ->assertSee('ne sono già stati confermati 12');

        $this->assertSame(20, (int) $opportunita->fresh()->total_packages);
    }

    #[Test]
    public function aumentare_la_disponibilita_e_sempre_possibile(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 10, ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 10);
        $this->assertTrue($opportunita->fresh()->isSoldOut());

        $this->form($buyer, $opportunita->fresh())
            ->set('total_packages', 30)
            ->call('salvaBozza')
            ->assertHasNoErrors();

        $this->assertSame(20, $opportunita->fresh()->remainingPackages());
    }

    #[Test]
    public function non_si_tolgono_punti_vendita_che_hanno_gia_risposto(): void
    {
        $buyer = $this->buyer();
        [$storeA, $crA] = $this->storeWithCr('PV801');
        [$storeB, $crB] = $this->storeWithCr('PV802');
        $opportunita = $this->openOpportunity([$storeA, $storeB], ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $storeA, $crA, 2);

        $this->form($buyer, $opportunita->fresh())
            ->set('store_ids', [$storeB->id])
            ->call('salvaBozza')
            ->assertHasErrors('salvataggio')
            ->assertSee('PV801');

        $this->assertCount(2, $opportunita->fresh()->stores);
    }

    #[Test]
    public function un_punto_vendita_che_non_ha_risposto_puo_essere_tolto(): void
    {
        $buyer = $this->buyer();
        [$storeA, $crA] = $this->storeWithCr('PV811');
        [$storeB, $crB] = $this->storeWithCr('PV812');
        $opportunita = $this->openOpportunity([$storeA, $storeB], ['created_by' => $buyer->id]);

        $this->form($buyer, $opportunita->fresh())
            ->set('store_ids', [$storeA->id])
            ->call('salvaBozza')
            ->assertHasNoErrors();

        $this->assertCount(1, $opportunita->fresh()->stores);
    }

    #[Test]
    public function unopportunita_in_verifica_resta_bloccata(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create(['created_by' => $buyer->id]);
        $opportunita->stores()->sync([$store->id]);

        $this->assertFalse($buyer->can('update', $opportunita));

        $this->actingAs($buyer)->get(route('buyer.opportunita.edit', $opportunita))->assertForbidden();
    }

    #[Test]
    public function unopportunita_annullata_o_chiusa_non_si_modifica(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        foreach ([OpportunityStatus::ANNULLATA, OpportunityStatus::CHIUSA, OpportunityStatus::ARCHIVIATA] as $stato) {
            $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id, 'status' => $stato]);

            $this->assertFalse($buyer->can('update', $opportunita), $stato->value);
        }
    }

    #[Test]
    public function la_scheda_mostra_il_collegamento_di_modifica_anche_da_aperta(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $this->actingAs($buyer)
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee(route('buyer.opportunita.edit', $opportunita), escape: false);
    }

    #[Test]
    public function il_modulo_avvisa_che_lopportunita_e_gia_pubblicata(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $this->form($buyer, $opportunita)
            ->assertSee('Opportunità già pubblicata')
            ->assertSee('torna in verifica')
            ->assertSee('Salva e ripubblica')
            ->assertDontSee('Invia in verifica');
    }
}
