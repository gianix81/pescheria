<?php

namespace Tests\Feature;

use App\Livewire\Cr\Opportunita;
use App\Livewire\Cr\Scheda;
use App\Models\Response;
use App\Services\ResponseSubmissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Regola di prodotto: dentro un'opportunità tutti i punti vendita destinatari
 * vedono quanto hanno ordinato gli altri, per creare emulazione fra i reparti.
 *
 * Vedere però non è agire: si può modificare solo la propria risposta, e chi
 * non è destinatario continua a non vedere nulla.
 */
class VisibilitaFraPuntiVenditaTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function un_cr_vede_le_quantita_ordinate_dagli_altri(): void
    {
        [$mio, $ioStesso] = $this->storeWithCr('PV701');
        [$altro, $crAltro] = $this->storeWithCr('PV702');
        [$terzo, $crTerzo] = $this->storeWithCr('PV703');

        $opportunita = $this->openOpportunity([$mio, $altro, $terzo], ['kg_per_package' => 5.0]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $altro, $crAltro, 7);
        $service->submitRefusal($opportunita->fresh(), $terzo, $crTerzo, 'Assortimento coperto');

        Livewire::actingAs($ioStesso)
            ->test(Scheda::class, ['opportunity' => $opportunita->fresh()])
            ->assertSee('Ordini degli altri punti vendita')
            ->assertSee('PV702')
            ->assertSee('7 colli')
            ->assertSee('PV703')
            ->assertSee('Rifiuto');
    }

    #[Test]
    public function la_classifica_e_ordinata_per_quantita_e_segnala_il_proprio_punto_vendita(): void
    {
        [$mio, $io] = $this->storeWithCr('PV711');
        [$grande, $crGrande] = $this->storeWithCr('PV712');
        [$piccolo, $crPiccolo] = $this->storeWithCr('PV713');

        $opportunita = $this->openOpportunity([$mio, $grande, $piccolo]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $piccolo, $crPiccolo, 2);
        $service->submitPurchase($opportunita->fresh(), $grande, $crGrande, 9);
        $service->submitPurchase($opportunita->fresh(), $mio, $io, 4);

        $componente = Livewire::actingAs($io)->test(Scheda::class, ['opportunity' => $opportunita->fresh()]);
        $classifica = $componente->instance()->classifica();

        $this->assertSame(['PV712', 'PV711', 'PV713'], $classifica->pluck('store.code')->all());
        $this->assertSame([9, 4, 2], $classifica->pluck('colli')->all());

        // Il proprio punto vendita è contrassegnato.
        $this->assertTrue($classifica->firstWhere('store.code', 'PV711')['proprio']);
        $this->assertFalse($classifica->firstWhere('store.code', 'PV712')['proprio']);

        $componente->assertSee('Tu');
    }

    #[Test]
    public function i_totali_complessivi_sono_visibili_al_cr(): void
    {
        [$mio, $io] = $this->storeWithCr('PV721');
        [$altro, $crAltro] = $this->storeWithCr('PV722');

        $opportunita = $this->openOpportunity([$mio, $altro], ['kg_per_package' => 4.0]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $altro, $crAltro, 3);
        $service->submitPurchase($opportunita->fresh(), $mio, $io, 2);

        Livewire::actingAs($io)
            ->test(Scheda::class, ['opportunity' => $opportunita->fresh()])
            ->assertSee('5')                 // 3 + 2 colli
            ->assertSee('20,00 kg')          // 5 colli × 4 kg
            ->assertSee('da 2 punti vendita');
    }

    #[Test]
    public function la_dashboard_mostra_quanto_hanno_gia_ordinato_gli_altri(): void
    {
        [$mio, $io] = $this->storeWithCr('PV731');
        [$altro, $crAltro] = $this->storeWithCr('PV732');

        $opportunita = $this->openOpportunity([$mio, $altro]);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $altro, $crAltro, 6);

        Livewire::actingAs($io)
            ->test(Opportunita::class)
            ->assertSee('Già ordinati')
            ->assertSee('6 colli');
    }

    #[Test]
    public function vedere_non_significa_poter_modificare(): void
    {
        [$mio, $io] = $this->storeWithCr('PV741');
        [$altro, $crAltro] = $this->storeWithCr('PV742');

        $opportunita = $this->openOpportunity([$mio, $altro]);
        $rispostaAltrui = app(ResponseSubmissionService::class)->submitPurchase($opportunita, $altro, $crAltro, 5);

        // La vede…
        $this->assertTrue($io->can('view', $rispostaAltrui));
        // …ma non può toccarla.
        $this->assertFalse($io->can('update', $rispostaAltrui));
        $this->assertTrue($io->can('update', app(ResponseSubmissionService::class)->findOrCreate($opportunita, $mio)));
    }

    #[Test]
    public function un_punto_vendita_non_destinatario_continua_a_non_vedere_nulla(): void
    {
        [$destinatario, $crDestinatario] = $this->storeWithCr('PV751');
        [$estraneo, $crEstraneo] = $this->storeWithCr('PV752');

        $opportunita = $this->openOpportunity([$destinatario]);
        $risposta = app(ResponseSubmissionService::class)->submitPurchase($opportunita, $destinatario, $crDestinatario, 3);

        $this->assertFalse($crEstraneo->can('view', $risposta));

        $this->actingAs($crEstraneo)
            ->get(route('cr.opportunita.show', $opportunita))
            ->assertForbidden();
    }

    #[Test]
    public function il_nome_di_chi_ha_ordinato_non_e_esposto_agli_altri_punti_vendita(): void
    {
        [$mio, $io] = $this->storeWithCr('PV761');
        [$altro, $crAltro] = $this->storeWithCr('PV762');

        $crAltro->forceFill(['first_name' => 'Ortensia', 'last_name' => 'Peculiare'])->save();

        $opportunita = $this->openOpportunity([$mio, $altro]);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $altro, $crAltro, 4);

        // La competizione è fra negozi, non fra persone: si vede il PdV, non il nome.
        Livewire::actingAs($io)
            ->test(Scheda::class, ['opportunity' => $opportunita->fresh()])
            ->assertSee('PV762')
            ->assertDontSee('Ortensia')
            ->assertDontSee('Peculiare');
    }

    #[Test]
    public function buyer_e_tecnico_continuano_a_vedere_tutto_compresi_i_nomi(): void
    {
        [$store, $cr] = $this->storeWithCr('PV771');
        $cr->forceFill(['first_name' => 'Ortensia', 'last_name' => 'Peculiare'])->save();

        $opportunita = $this->openOpportunity([$store]);
        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 3);

        foreach ([$this->buyer(), $this->tecnico()] as $utente) {
            $this->actingAs($utente)
                ->get(route('opportunita.show', $opportunita))
                ->assertOk()
                ->assertSee('PV771')
                ->assertSee('Ortensia Peculiare');
        }
    }

    #[Test]
    public function restano_validi_i_limiti_sulle_azioni_altrui(): void
    {
        [$mio, $io] = $this->storeWithCr('PV781');
        [$altro] = $this->storeWithCr('PV782');

        $opportunita = $this->openOpportunity([$mio, $altro]);

        $rispostaAltrui = Response::factory()->create([
            'opportunity_id' => $opportunita->id,
            'store_id' => $altro->id,
        ]);

        // Vedere gli ordini altrui non abilita a ordinare per loro conto.
        $this->expectException(AuthorizationException::class);

        Gate::forUser($io)->authorize('update', $rispostaAltrui);
    }
}
