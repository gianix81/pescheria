<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class PermessiRuoliTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function il_login_atterra_sulla_home_del_ruolo(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($this->buyer())->get('/')->assertRedirect(route('buyer.dashboard'));
        $this->actingAs($this->tecnico())->get('/')->assertRedirect(route('tecnico.dashboard'));
        $this->actingAs($cr)->get('/')->assertRedirect(route('cr.opportunita.index'));
    }

    #[Test]
    public function le_pagine_del_buyer_sono_vietate_agli_altri_ruoli(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($this->buyer())->get(route('buyer.dashboard'))->assertOk();
        $this->actingAs($this->tecnico())->get(route('buyer.dashboard'))->assertForbidden();
        $this->actingAs($cr)->get(route('buyer.dashboard'))->assertForbidden();
    }

    #[Test]
    public function le_pagine_del_tecnico_sono_vietate_agli_altri_ruoli(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($this->tecnico())->get(route('tecnico.dashboard'))->assertOk();
        $this->actingAs($this->buyer())->get(route('tecnico.dashboard'))->assertForbidden();
        $this->actingAs($cr)->get(route('tecnico.monitor'))->assertForbidden();
        $this->actingAs($this->buyer())->get(route('tecnico.anagrafiche.utenti'))->assertForbidden();
    }

    #[Test]
    public function il_capo_reparto_non_accede_alle_pagine_di_buyer_e_tecnico(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get(route('cr.opportunita.index'))->assertOk();
        $this->actingAs($cr)->get(route('opportunita.index'))->assertForbidden();
        $this->actingAs($cr)->get(route('export.index'))->assertForbidden();
        $this->actingAs($cr)->get(route('export.csv'))->assertForbidden();
    }

    #[Test]
    public function un_capo_reparto_non_vede_le_opportunita_di_un_altro_punto_vendita(): void
    {
        [$storeA, $crA] = $this->storeWithCr('PV900');
        [$storeB, $crB] = $this->storeWithCr('PV901');

        $opportunita = $this->openOpportunity([$storeB]);

        $this->actingAs($crA)->get(route('cr.opportunita.show', $opportunita))->assertForbidden();
        $this->actingAs($crB)->get(route('cr.opportunita.show', $opportunita))->assertOk();
    }

    #[Test]
    public function un_capo_reparto_non_vede_le_opportunita_non_ancora_pubblicate(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $bozza = $this->openOpportunity([$store], ['status' => OpportunityStatus::BOZZA]);
        $inVerifica = $this->openOpportunity([$store], ['status' => OpportunityStatus::IN_VERIFICA]);

        $this->actingAs($cr)->get(route('cr.opportunita.show', $bozza))->assertForbidden();
        $this->actingAs($cr)->get(route('cr.opportunita.show', $inVerifica))->assertForbidden();

        $visibili = Opportunity::visibleTo($cr)->pluck('id');
        $this->assertCount(0, $visibili);
    }

    #[Test]
    public function un_utente_disattivato_non_puo_accedere(): void
    {
        $buyer = $this->buyer();
        $buyer->forceFill(['is_active' => false])->save();

        $this->post(route('login'), ['email' => $buyer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function il_login_valido_registra_accesso_e_audit(): void
    {
        $buyer = $this->buyer();

        $this->post(route('login'), ['email' => $buyer->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($buyer);
        $this->assertNotNull($buyer->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login', 'user_id' => $buyer->id]);
    }

    #[Test]
    public function le_rotte_protette_richiedono_autenticazione(): void
    {
        $this->get(route('buyer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('tecnico.dashboard'))->assertRedirect(route('login'));
        $this->get(route('cr.opportunita.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function il_cambio_password_obbligatorio_blocca_la_navigazione(): void
    {
        $buyer = $this->buyer();
        $buyer->forceFill(['must_change_password' => true])->save();

        $this->actingAs($buyer)->get(route('buyer.dashboard'))->assertRedirect(route('password.change'));

        $this->actingAs($buyer)->post(route('password.change.update'), [
            'current_password' => 'password',
            'password' => 'NuovaPassword1',
            'password_confirmation' => 'NuovaPassword1',
        ])->assertRedirect(route('home'));

        $this->assertFalse($buyer->fresh()->must_change_password);
    }
}
