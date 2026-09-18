<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Livewire\Cr\Scheda;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/** Smoke test: ogni pagina principale deve rendersi senza errori. */
class PagineTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function le_pagine_del_buyer_si_rendono(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $buyer = $this->buyer();
        $opportunita = $this->openOpportunity([$store]);
        $bozza = Opportunity::factory()->create(['created_by' => $buyer->id, 'status' => OpportunityStatus::BOZZA]);

        foreach ([
            route('buyer.dashboard'),
            route('buyer.opportunita.create'),
            route('buyer.opportunita.edit', $bozza),
            route('buyer.ordini'),
            route('opportunita.index'),
            route('opportunita.show', $opportunita),
            route('storico'),
            route('export.index'),
            route('notifiche.index'),
        ] as $url) {
            $this->actingAs($buyer)->get($url)->assertOk();
        }
    }

    #[Test]
    public function le_pagine_del_tecnico_si_rendono(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $tecnico = $this->tecnico();
        $inVerifica = Opportunity::factory()->inVerifica()->create();
        $inVerifica->stores()->sync([$store->id]);

        foreach ([
            route('tecnico.dashboard'),
            route('tecnico.monitor'),
            route('tecnico.audit'),
            route('tecnico.anagrafiche.utenti'),
            route('tecnico.anagrafiche.punti-vendita'),
            route('tecnico.anagrafiche.prodotti'),
            route('tecnico.verifica', $inVerifica),
            route('opportunita.index', ['preset' => 'da_verificare']),
            route('export.index'),
        ] as $url) {
            $this->actingAs($tecnico)->get($url)->assertOk();
        }
    }

    #[Test]
    public function le_pagine_del_capo_reparto_si_rendono(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        foreach ([
            route('cr.opportunita.index'),
            route('cr.opportunita.index', ['vista' => 'inviate']),
            route('cr.opportunita.index', ['vista' => 'storico']),
            route('cr.opportunita.show', $opportunita),
            route('notifiche.index'),
        ] as $url) {
            $this->actingAs($cr)->get($url)->assertOk();
        }
    }

    #[Test]
    public function la_scheda_cr_mostra_i_dati_chiave_e_i_pulsanti_rapidi(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['kg_per_package' => 5.0]);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee($opportunita->article_code)
            ->assertSee('Acquista')
            ->assertSee('Non acquista');

        // I pulsanti rapidi compaiono dopo aver scelto "Acquista".
        Livewire::actingAs($cr)
            ->test(Scheda::class, ['opportunity' => $opportunita])
            ->set('decisione', 'acquista')
            ->assertSee('Altra quantità')
            ->call('scegliColli', 3)
            ->assertSet('colli', 3)
            ->assertSee('15,00 kg');
    }

    #[Test]
    public function le_pagine_di_login_si_rendono(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Accedi');
        $this->get(route('password.request'))->assertOk();
        $this->get(route('password.reset', ['token' => 'abc']))->assertOk();
    }
}
