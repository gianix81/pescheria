<?php

namespace Tests\Feature;

use App\Exceptions\DomainException;
use App\Livewire\Shared\OpportunitaShow;
use App\Models\AuditLog;
use App\Models\OpportunityMedia;
use App\Services\MediaService;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Eliminazione definitiva: non è l'annullamento, qui sparisce tutto.
 * La motivazione è obbligatoria finché l'opportunità è recente, superflua se il
 * termine è passato da oltre un mese.
 */
class EliminazioneOpportunitaTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    private function servizio(): OpportunityWorkflowService
    {
        return app(OpportunityWorkflowService::class);
    }

    #[Test]
    public function unopportunita_recente_richiede_la_motivazione(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        try {
            $this->servizio()->eliminaDefinitivamente($opportunita, $buyer, '   ');
            $this->fail('Doveva essere richiesta la motivazione.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Serve una motivazione', $e->getMessage());
        }

        $this->assertDatabaseHas('opportunities', ['id' => $opportunita->id]);
    }

    #[Test]
    public function con_la_motivazione_lopportunita_sparisce_del_tutto(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        $percorso = $media->path;

        $risposta = app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 3);

        $this->servizio()->eliminaDefinitivamente($opportunita->fresh(), $buyer, 'Creata per errore');

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunita->id]);
        $this->assertDatabaseMissing('responses', ['id' => $risposta->id]);
        $this->assertDatabaseMissing('opportunity_media', ['id' => $media->id]);
        $this->assertDatabaseMissing('opportunity_stores', ['opportunity_id' => $opportunita->id]);
        $this->assertDatabaseMissing('response_revisions', ['response_id' => $risposta->id]);

        // Anche il file esce dal disco.
        Storage::disk(config('pescheria.media.disk'))->assertMissing($percorso);
    }

    #[Test]
    public function scaduta_da_oltre_un_mese_non_serve_giustificarsi(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], [
            'created_by' => $buyer->id,
            'opens_at' => now()->subMonths(3),
            'closes_at' => now()->subMonths(2),
        ]);

        $this->assertTrue($opportunita->eliminabileSenzaMotivazione());

        $this->servizio()->eliminaDefinitivamente($opportunita, $buyer, null);

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunita->id]);
    }

    #[Test]
    public function scaduta_da_meno_di_un_mese_la_motivazione_serve_ancora(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], [
            'created_by' => $buyer->id,
            'opens_at' => now()->subDays(20),
            'closes_at' => now()->subDays(15),
        ]);

        $this->assertFalse($opportunita->eliminabileSenzaMotivazione());

        $this->expectException(DomainException::class);
        $this->servizio()->eliminaDefinitivamente($opportunita, $buyer, null);
    }

    #[Test]
    public function laudit_conserva_la_traccia_di_cosa_e_stato_eliminato(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], [
            'created_by' => $buyer->id,
            'article_code' => 'ART77777',
        ]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 4);

        $this->servizio()->eliminaDefinitivamente($opportunita->fresh(), $buyer, 'Doppione');

        $voce = AuditLog::where('action', 'opportunity.deleted')->firstOrFail();

        $this->assertSame($opportunita->reference, $voce->payload['riferimento']);
        $this->assertSame('ART77777', $voce->payload['articolo']);
        $this->assertSame(1, $voce->payload['risposte_inviate']);
        $this->assertSame(4, $voce->payload['colli_ordinati']);
        $this->assertSame('Doppione', $voce->payload['motivazione']);
        $this->assertSame($buyer->id, $voce->user_id);
    }

    #[Test]
    public function laudit_dice_anche_quando_la_motivazione_non_era_richiesta(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], [
            'created_by' => $buyer->id,
            'opens_at' => now()->subMonths(3),
            'closes_at' => now()->subMonths(2),
        ]);

        $this->servizio()->eliminaDefinitivamente($opportunita, $buyer, null);

        $voce = AuditLog::where('action', 'opportunity.deleted')->firstOrFail();

        $this->assertStringContainsString('non richiesta', $voce->payload['motivazione']);
    }

    #[Test]
    public function solo_il_buyer_puo_eliminare(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->assertTrue($this->buyer()->can('delete', $opportunita));
        $this->assertTrue($this->admin()->can('delete', $opportunita));
        $this->assertFalse($this->tecnico()->can('delete', $opportunita));
        $this->assertFalse($cr->can('delete', $opportunita));
    }

    #[Test]
    public function dallinterfaccia_la_conferma_dice_cosa_si_perde(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 5);

        Livewire::actingAs($buyer)
            ->test(OpportunitaShow::class, ['opportunity' => $opportunita->fresh()])
            ->call('apriAzione', 'elimina')
            ->assertSee('Eliminare definitivamente?')
            ->assertSee('non si annulla')
            ->assertSee('Non è scaduta da almeno un mese');
    }

    #[Test]
    public function dallinterfaccia_si_elimina_e_si_torna_allelenco(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        Livewire::actingAs($buyer)
            ->test(OpportunitaShow::class, ['opportunity' => $opportunita])
            ->call('apriAzione', 'elimina')
            ->set('motivazione', 'Creata per errore')
            ->call('conferma')
            ->assertRedirect(route('opportunita.index'));

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunita->id]);
    }

    #[Test]
    public function senza_motivazione_linterfaccia_lo_dice_e_non_elimina(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        Livewire::actingAs($buyer)
            ->test(OpportunitaShow::class, ['opportunity' => $opportunita])
            ->call('apriAzione', 'elimina')
            ->call('conferma')
            ->assertHasErrors('azione');

        $this->assertDatabaseHas('opportunities', ['id' => $opportunita->id]);
    }
}
