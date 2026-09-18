<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Exceptions\DomainException;
use App\Exceptions\InvalidTransitionException;
use App\Exceptions\ResponseWindowClosedException;
use App\Models\AuditLog;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class WorkflowOpportunitaTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private OpportunityWorkflowService $workflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = app(OpportunityWorkflowService::class);
    }

    private function bozzaCompleta(array $stores, array $attributi = []): Opportunity
    {
        $opportunita = Opportunity::factory()->create($attributi + ['status' => OpportunityStatus::BOZZA]);
        $opportunita->stores()->sync(collect($stores)->pluck('id')->all());
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        return $opportunita->fresh(['stores', 'media']);
    }

    #[Test]
    public function il_percorso_completo_porta_da_bozza_ad_aperta(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();

        $opportunita = $this->bozzaCompleta([$store], ['created_by' => $buyer->id]);

        $this->workflow->submitForReview($opportunita, $buyer);
        $this->assertSame(OpportunityStatus::IN_VERIFICA, $opportunita->fresh()->status);

        $this->workflow->approve($opportunita->fresh(), $tecnico, 'Tutto conforme');

        $aggiornata = $opportunita->fresh();
        $this->assertSame(OpportunityStatus::APERTA, $aggiornata->status);
        $this->assertNotNull($aggiornata->approved_at);
        $this->assertSame($tecnico->id, $aggiornata->reviewed_by);

        // All'apertura ogni destinatario ha una riga di risposta NON_COMPILATA.
        $this->assertDatabaseHas('responses', [
            'opportunity_id' => $opportunita->id,
            'store_id' => $store->id,
            'status' => 'NON_COMPILATA',
        ]);
    }

    #[Test]
    public function approvare_con_apertura_futura_produce_lo_stato_programmata(): void
    {
        [$store] = $this->storeWithCr();

        $opportunita = $this->bozzaCompleta([$store], [
            'opens_at' => now()->addHours(5),
            'closes_at' => now()->addHours(12),
        ]);

        $this->workflow->submitForReview($opportunita, $this->buyer());
        $this->workflow->approve($opportunita->fresh(), $this->tecnico());

        $this->assertSame(OpportunityStatus::PROGRAMMATA, $opportunita->fresh()->status);
    }

    #[Test]
    public function il_rifiuto_richiede_una_motivazione_e_riporta_a_da_correggere(): void
    {
        [$store] = $this->storeWithCr();
        $tecnico = $this->tecnico();
        $opportunita = $this->bozzaCompleta([$store]);

        $this->workflow->submitForReview($opportunita, $this->buyer());

        try {
            $this->workflow->reject($opportunita->fresh(), $tecnico, '   ');
            $this->fail('Il rifiuto senza motivazione doveva essere respinto.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('motivazione', $e->getMessage());
        }

        $this->workflow->reject($opportunita->fresh(), $tecnico, 'Prezzo di vendita incoerente');

        $aggiornata = $opportunita->fresh();
        $this->assertSame(OpportunityStatus::DA_CORREGGERE, $aggiornata->status);
        $this->assertSame('Prezzo di vendita incoerente', $aggiornata->review_notes);
        $this->assertDatabaseHas('opportunity_reviews', [
            'opportunity_id' => $opportunita->id,
            'outcome' => 'RESPINTA',
        ]);
    }

    #[Test]
    public function non_si_pubblica_senza_media_salvo_eccezione_tracciata(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->create(['status' => OpportunityStatus::BOZZA]);
        $opportunita->stores()->sync([$store->id]);

        $problemi = $this->workflow->publishIssues($opportunita->fresh());
        $this->assertNotEmpty(array_filter($problemi, fn ($p) => str_contains($p, 'foto')));

        $this->workflow->grantMediaException($opportunita, $this->tecnico(), 'Video inviato via fornitore, non caricabile');

        $this->assertEmpty($this->workflow->publishIssues($opportunita->fresh()));
        $this->assertDatabaseHas('audit_logs', ['action' => 'opportunity.media_exception']);
    }

    #[Test]
    public function le_transizioni_non_valide_sono_bloccate(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = $this->bozzaCompleta([$store]);

        // Da BOZZA non si può approvare direttamente.
        $this->expectException(DomainException::class);
        $this->workflow->approve($opportunita, $this->tecnico());
    }

    #[Test]
    public function una_opportunita_chiusa_non_torna_aperta(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);
        $buyer = $this->buyer();

        $this->workflow->close($opportunita, $buyer, 'Quantitativo esaurito');
        $this->assertSame(OpportunityStatus::CHIUSA, $opportunita->fresh()->status);

        $this->expectException(InvalidTransitionException::class);
        $this->workflow->cancel($opportunita->fresh(), $buyer, 'Ripensamento');
    }

    #[Test]
    public function il_comando_di_apertura_e_idempotente(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = $this->bozzaCompleta([$store], [
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHours(4),
            'status' => OpportunityStatus::PROGRAMMATA,
        ]);

        $this->artisan('opportunita:apri')->assertSuccessful();
        $this->assertSame(OpportunityStatus::APERTA, $opportunita->fresh()->status);

        $primaModifica = $opportunita->fresh()->published_at;

        $this->artisan('opportunita:apri')->assertSuccessful();
        $this->assertEquals($primaModifica, $opportunita->fresh()->published_at);
    }

    #[Test]
    public function il_comando_di_scadenza_chiude_le_risposte(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->addMinutes(5)]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 2);

        $opportunita->forceFill(['closes_at' => now()->subMinute()])->save();

        $this->artisan('opportunita:scadi')->assertSuccessful();

        $this->assertSame(OpportunityStatus::SCADUTA, $opportunita->fresh()->status);

        // Dopo la scadenza il CR non può più inviare.
        $this->expectException(ResponseWindowClosedException::class);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita->fresh(), $store, $cr, 3);
    }

    #[Test]
    public function la_duplicazione_copia_articolo_e_prezzi_ma_non_risposte_e_stock(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $buyer = $this->buyer();
        $originale = $this->limitedOpportunity([$store], 10);

        app(ResponseSubmissionService::class)->submitPurchase($originale, $store, $cr, 4);

        $copia = $this->workflow->duplicate($originale->fresh(), $buyer);

        $this->assertNotSame($originale->reference, $copia->reference);
        $this->assertSame($originale->article_code, $copia->article_code);
        $this->assertEquals($originale->purchase_price, $copia->purchase_price);
        $this->assertSame(OpportunityStatus::BOZZA, $copia->status);
        $this->assertSame(0, (int) $copia->committed_packages);
        $this->assertNull($copia->total_packages);
        $this->assertSame(0, $copia->responses()->count());
        $this->assertEqualsCanonicalizing(
            $originale->stores->pluck('id')->all(),
            $copia->stores->pluck('id')->all(),
        );
    }

    #[Test]
    public function annullare_libera_lo_stock_impegnato(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 10);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 6);
        $this->assertSame(6, (int) $opportunita->fresh()->committed_packages);

        $this->workflow->cancel($opportunita->fresh(), $this->buyer(), 'Prodotto non conforme');

        $this->assertSame(OpportunityStatus::ANNULLATA, $opportunita->fresh()->status);
        $this->assertSame(0, (int) $opportunita->fresh()->committed_packages);
    }

    #[Test]
    public function ogni_transizione_finisce_nellaudit_log(): void
    {
        [$store] = $this->storeWithCr();
        $buyer = $this->buyer();
        $opportunita = $this->bozzaCompleta([$store], ['created_by' => $buyer->id]);

        $this->workflow->submitForReview($opportunita, $buyer);
        $this->workflow->approve($opportunita->fresh(), $this->tecnico());

        $this->assertDatabaseHas('audit_logs', ['action' => 'opportunity.transition']);
        $this->assertGreaterThanOrEqual(2, AuditLog::where('action', 'opportunity.transition')->count());
    }

    #[Test]
    public function laudit_log_non_puo_essere_modificato(): void
    {
        $voce = AuditLog::create([
            'action' => 'test', 'payload' => [], 'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $voce->update(['action' => 'manomesso']);
    }
}
