<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Tecnico\Anagrafiche\Utenti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Nell'interfaccia il ruolo che ordina per il negozio si chiama «Punto vendita».
 * Il valore interno resta CAPO_REPARTO: rinominarlo avrebbe richiesto di
 * migrare i dati senza che nulla cambiasse a schermo.
 */
class DicituraRuoliTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function il_ruolo_si_chiama_punto_vendita(): void
    {
        $this->assertSame('Punto vendita', Role::CAPO_REPARTO->label());
        $this->assertSame('CAPO_REPARTO', Role::CAPO_REPARTO->value);
    }

    #[Test]
    public function la_dicitura_non_compare_piu_nelle_pagine(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $pagine = [
            [$cr, route('cr.opportunita.index')],
            [$cr, route('cr.opportunita.show', $opportunita)],
            [$this->admin(), route('tecnico.anagrafiche.utenti')],
            [$this->admin(), route('opportunita.show', $opportunita)],
            [$this->buyer(), route('buyer.opportunita.create')],
        ];

        foreach ($pagine as [$utente, $url]) {
            $risposta = $this->actingAs($utente)->get($url)->assertOk();

            $risposta->assertDontSee('Capo Reparto');
            $risposta->assertDontSee('capo reparto');
            $risposta->assertDontSee('capi reparto');
        }
    }

    #[Test]
    public function la_barra_laterale_mostra_la_nuova_dicitura(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)
            ->get(route('cr.opportunita.index'))
            ->assertOk()
            ->assertSee('Punto vendita');
    }

    #[Test]
    public function la_gestione_utenti_propone_il_ruolo_col_nuovo_nome(): void
    {
        $this->actingAs($this->admin())
            ->get(route('tecnico.anagrafiche.utenti'))
            ->assertOk()
            ->assertSee('Punto vendita')
            ->assertSee('Ordina per il proprio punto vendita');
    }

    #[Test]
    public function il_messaggio_sul_punto_vendita_obbligatorio_non_si_ripete(): void
    {
        // Prima diceva «Un Capo Reparto deve essere associato a un punto vendita»:
        // con la nuova dicitura sarebbe diventato «Un Punto vendita deve essere
        // associato a un punto vendita».
        Livewire::actingAs($this->admin())
            ->test(Utenti::class)
            ->set('form.first_name', 'Mario')
            ->set('form.last_name', 'Rossi')
            ->set('form.email', 'mario@pescheria.local')
            ->set('form.role', 'CAPO_REPARTO')
            ->set('form.store_id', null)
            ->set('password', 'PasswordSicura1')
            ->call('salva')
            ->assertHasErrors('form.store_id')
            ->assertSee('Scegli il punto vendita per cui questo utente ordina');
    }
}
