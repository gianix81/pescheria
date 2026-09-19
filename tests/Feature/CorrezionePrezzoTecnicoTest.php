<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\OpportunityStatus;
use App\Exceptions\DomainException;
use App\Livewire\Tecnico\Verifica;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\OpportunityWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Il Tecnico corregge il prezzo di vendita mentre verifica, prima che
 * l'opportunità raggiunga i punti vendita. È l'unico dato che tocca
 * direttamente, e ricarico e margine si ricalcolano di conseguenza.
 */
class CorrezionePrezzoTecnicoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private function inVerifica(array $attributi = []): Opportunity
    {
        [$store] = $this->storeWithCr();

        $opportunita = Opportunity::factory()->inVerifica()->create($attributi + [
            'purchase_price' => 5.00,
            'sale_price_gross' => 7.98,
            'vat_rate' => 10,
        ]);
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        return $opportunita->fresh(['stores', 'media']);
    }

    #[Test]
    public function correggere_il_prezzo_ricalcola_ricarico_e_margine(): void
    {
        $opportunita = $this->inVerifica();
        $tecnico = $this->tecnico();

        // Prima: 5,00 → 7,98 con IVA 10% dà ricarico ~45%.
        $this->assertEqualsWithDelta(45.09, (float) $opportunita->markup_percent, 0.02);

        app(OpportunityWorkflowService::class)
            ->aggiornaPrezzoVendita($opportunita, $tecnico, 9.90, 'Allineato al listino');

        $aggiornata = $opportunita->fresh();

        $this->assertEqualsWithDelta(9.90, (float) $aggiornata->sale_price_gross, 0.001);
        // 9,90 / 1,10 = 9,00 netto → ricarico 80%, margine ~44,4%.
        $this->assertEqualsWithDelta(80.00, (float) $aggiornata->markup_percent, 0.05);
        $this->assertEqualsWithDelta(44.44, (float) $aggiornata->margin_percent, 0.05);
    }

    #[Test]
    public function la_correzione_finisce_nellaudit_con_prima_e_dopo(): void
    {
        $opportunita = $this->inVerifica();
        $tecnico = $this->tecnico();

        app(OpportunityWorkflowService::class)
            ->aggiornaPrezzoVendita($opportunita, $tecnico, 9.90, 'Allineato al listino');

        $voce = AuditLog::where('action', 'opportunity.price_updated')->firstOrFail();

        $this->assertEqualsWithDelta(7.98, (float) $voce->payload['prezzo_prima'], 0.001);
        $this->assertEqualsWithDelta(9.90, (float) $voce->payload['prezzo_dopo'], 0.001);
        $this->assertSame('Allineato al listino', $voce->payload['nota']);
        $this->assertSame($tecnico->id, $voce->user_id);
    }

    #[Test]
    public function il_buyer_viene_avvisato(): void
    {
        $buyer = $this->buyer();
        $opportunita = $this->inVerifica(['created_by' => $buyer->id]);

        app(OpportunityWorkflowService::class)
            ->aggiornaPrezzoVendita($opportunita, $this->tecnico(), 9.90);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => NotificationType::OPPORTUNITA_MODIFICATA->value,
        ]);

        $notifica = Notification::where('user_id', $buyer->id)->latest('id')->first();
        $this->assertStringContainsString('Prezzo corretto dal Tecnico', $notifica->title);
        $this->assertStringContainsString('€ 7,98', $notifica->body);
        $this->assertStringContainsString('€ 9,90', $notifica->body);
    }

    #[Test]
    public function fuori_dalla_verifica_il_prezzo_non_si_tocca(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $aperta = $this->openOpportunity([$store], ['sale_price_gross' => 7.98]);

        $this->expectException(DomainException::class);

        app(OpportunityWorkflowService::class)->aggiornaPrezzoVendita($aperta, $this->tecnico(), 9.90);
    }

    #[Test]
    public function un_prezzo_non_positivo_viene_rifiutato(): void
    {
        $opportunita = $this->inVerifica();

        $this->expectException(DomainException::class);

        app(OpportunityWorkflowService::class)->aggiornaPrezzoVendita($opportunita, $this->tecnico(), 0);
    }

    #[Test]
    public function lo_stesso_prezzo_non_lascia_tracce_inutili(): void
    {
        $opportunita = $this->inVerifica();

        app(OpportunityWorkflowService::class)->aggiornaPrezzoVendita($opportunita, $this->tecnico(), 7.98);

        $this->assertSame(0, AuditLog::where('action', 'opportunity.price_updated')->count());
    }

    #[Test]
    public function dalla_schermata_di_verifica_si_vede_leffetto_prima_di_salvare(): void
    {
        $opportunita = $this->inVerifica();

        $componente = Livewire::actingAs($this->tecnico())
            ->test(Verifica::class, ['opportunity' => $opportunita])
            ->assertSet('prezzoVendita', 7.98)
            ->set('prezzoVendita', 9.90);

        // L'anteprima si aggiorna senza aver ancora salvato.
        $proposti = $componente->instance()->prezziProposti;
        $this->assertEqualsWithDelta(80.00, $proposti['markup'], 0.05);
        $this->assertEqualsWithDelta(7.98, (float) $opportunita->fresh()->sale_price_gross, 0.001);

        $componente->call('aggiornaPrezzo')->assertHasNoErrors();

        $this->assertEqualsWithDelta(9.90, (float) $opportunita->fresh()->sale_price_gross, 0.001);
    }

    #[Test]
    public function il_pannello_compare_solo_al_tecnico_e_solo_in_verifica(): void
    {
        $opportunita = $this->inVerifica();

        $this->assertTrue($this->tecnico()->can('updatePrice', $opportunita));
        $this->assertFalse($this->buyer()->can('updatePrice', $opportunita));

        $this->actingAs($this->tecnico())
            ->get(route('tecnico.verifica', $opportunita))
            ->assertOk()
            ->assertSee('Prezzo di vendita')
            ->assertSee('Aggiorna prezzo');
    }

    #[Test]
    public function il_prezzo_corretto_e_quello_che_vedono_i_punti_vendita(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create([
            'purchase_price' => 5.00,
            'sale_price_gross' => 7.98,
            'vat_rate' => 10,
        ]);
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        $servizio = app(OpportunityWorkflowService::class);
        $servizio->aggiornaPrezzoVendita($opportunita->fresh(['stores', 'media']), $this->tecnico(), 9.90);
        $servizio->approve($opportunita->fresh(['stores', 'media']), $this->tecnico());

        $this->assertSame(OpportunityStatus::APERTA, $opportunita->fresh()->status);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('€ 9,90');
    }
}
