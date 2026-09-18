<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Enums\OpportunityStatus;
use App\Livewire\Buyer\OpportunitaForm;
use App\Livewire\Tecnico\Verifica;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class FlussoBuyerTecnicoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    #[Test]
    public function la_creazione_rapida_produce_una_bozza_completa(): void
    {
        $buyer = $this->buyer();
        $prodotto = Product::factory()->create(['default_kg_per_package' => 6.0, 'vat_rate' => 10]);
        [$store, $cr] = $this->storeWithCr();

        $componente = Livewire::actingAs($buyer)
            ->test(OpportunitaForm::class)
            ->set('ricercaArticolo', $prodotto->article_code)
            ->call('selezionaProdotto', $prodotto->id)
            ->assertSet('article_code', $prodotto->article_code)
            ->assertSet('plu', (string) $prodotto->plu)
            ->set('purchase_price', 5.00)
            ->set('sale_price_gross', 7.98)
            ->set('vat_rate', 10)
            ->set('kg_per_package', 6.0)
            ->set('availability_type', 'LIMITATA')
            ->set('total_packages', 30)
            ->set('store_ids', [$store->id])
            ->call('salvaBozza');

        $componente->assertHasNoErrors();

        $opportunita = Opportunity::firstWhere('article_code', $prodotto->article_code);

        $this->assertNotNull($opportunita);
        $this->assertSame(OpportunityStatus::BOZZA, $opportunita->status);
        $this->assertStringStartsWith('OPP-', $opportunita->reference);

        // Ricarico e margine sono calcolati, non digitati.
        $this->assertEqualsWithDelta(45.09, (float) $opportunita->markup_percent, 0.02);
        $this->assertEqualsWithDelta(31.08, (float) $opportunita->margin_percent, 0.02);
        $this->assertEqualsCanonicalizing([$store->id], $opportunita->stores->pluck('id')->all());
    }

    #[Test]
    public function la_scadenza_deve_essere_successiva_allapertura(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        Livewire::actingAs($buyer)
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ART1')
            ->set('description', 'Test')
            ->set('title', 'Test')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->set('opens_at', now()->addHours(5)->format('Y-m-d\TH:i'))
            ->set('closes_at', now()->addHour()->format('Y-m-d\TH:i'))
            ->call('salvaBozza')
            ->assertHasErrors('closes_at');
    }

    #[Test]
    public function la_disponibilita_limitata_richiede_il_totale_colli(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        Livewire::actingAs($buyer)
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ART2')
            ->set('description', 'Test')
            ->set('title', 'Test')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->set('availability_type', 'LIMITATA')
            ->set('total_packages', null)
            ->call('salvaBozza')
            ->assertHasErrors('total_packages');
    }

    #[Test]
    public function il_caricamento_di_un_video_allega_il_media_alla_bozza(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        $componente = Livewire::actingAs($buyer)
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ART3')
            ->set('description', 'Orata')
            ->set('title', 'Orata del giorno')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->set('nuoviMedia', [UploadedFile::fake()->create('verticale.mp4', 1024, 'video/mp4')])
            ->call('caricaMedia');

        $componente->assertHasNoErrors();

        $opportunita = Opportunity::firstWhere('article_code', 'ART3');
        $this->assertSame(1, $opportunita->media()->count());
        $this->assertSame(MediaType::VIDEO, $opportunita->media()->first()->type);
    }

    #[Test]
    public function linvio_in_verifica_richiede_almeno_un_media(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        Livewire::actingAs($buyer)
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ART4')
            ->set('description', 'Senza media')
            ->set('title', 'Senza media')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->call('inviaInVerifica')
            ->assertHasErrors('invio');

        $this->assertSame(OpportunityStatus::BOZZA, Opportunity::firstWhere('article_code', 'ART4')->status);
    }

    #[Test]
    public function il_tecnico_approva_dalla_schermata_di_verifica(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        Livewire::actingAs($this->tecnico())
            ->test(Verifica::class, ['opportunity' => $opportunita->fresh()])
            ->set('checklist', ['anagrafica' => true, 'prezzi' => true])
            ->set('note', 'Verificato con il fornitore')
            ->call('approva')
            ->assertRedirect(route('opportunita.show', $opportunita));

        $this->assertSame(OpportunityStatus::APERTA, $opportunita->fresh()->status);
        $this->assertDatabaseHas('opportunity_reviews', [
            'opportunity_id' => $opportunita->id,
            'outcome' => 'APPROVATA',
        ]);
    }

    #[Test]
    public function il_tecnico_respinge_indicando_la_motivazione(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$store->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        Livewire::actingAs($this->tecnico())
            ->test(Verifica::class, ['opportunity' => $opportunita->fresh()])
            ->set('motivazioneRifiuto', '')
            ->call('respingi')
            ->assertHasErrors('verifica');

        $this->assertSame(OpportunityStatus::IN_VERIFICA, $opportunita->fresh()->status);

        Livewire::actingAs($this->tecnico())
            ->test(Verifica::class, ['opportunity' => $opportunita->fresh()])
            ->set('motivazioneRifiuto', 'Peso per collo non corretto')
            ->call('respingi')
            ->assertRedirect(route('tecnico.dashboard'));

        $this->assertSame(OpportunityStatus::DA_CORREGGERE, $opportunita->fresh()->status);
    }

    #[Test]
    public function il_buyer_non_puo_verificare_le_proprie_opportunita(): void
    {
        [$store] = $this->storeWithCr();
        $opportunita = Opportunity::factory()->inVerifica()->create(['created_by' => $this->buyer()->id]);
        $opportunita->stores()->sync([$store->id]);

        $this->actingAs(Opportunity::find($opportunita->id)->creator)
            ->get(route('tecnico.verifica', $opportunita))
            ->assertForbidden();
    }

    #[Test]
    public function il_buyer_puo_modificare_solo_bozze_e_opportunita_da_correggere(): void
    {
        $buyer = $this->buyer();
        [$store] = $this->storeWithCr();

        $bozza = Opportunity::factory()->create(['status' => OpportunityStatus::BOZZA, 'created_by' => $buyer->id]);
        $inVerifica = Opportunity::factory()->inVerifica()->create(['created_by' => $buyer->id]);
        $daCorreggere = Opportunity::factory()->create(['status' => OpportunityStatus::DA_CORREGGERE, 'created_by' => $buyer->id]);

        $this->assertTrue($buyer->can('update', $bozza));
        $this->assertFalse($buyer->can('update', $inVerifica));
        $this->assertTrue($buyer->can('update', $daCorreggere));

        $this->actingAs($buyer)->get(route('buyer.opportunita.edit', $inVerifica))->assertForbidden();
    }
}
