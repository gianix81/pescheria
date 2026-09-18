<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * La pagina di primo accesso è una porta che si chiude da sola: esiste solo
 * finché non c'è un Super Admin attivo, e solo se il token è configurato e
 * corrisponde. In ogni altro caso deve rispondere 404, non 403: non deve
 * nemmeno rivelare che l'indirizzo esiste.
 */
class SetupInizialeTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private string $token = 'token-di-prova-lungo-abbastanza';

    #[Test]
    public function senza_token_configurato_la_pagina_non_esiste(): void
    {
        config(['pescheria.setup_token' => '']);

        $this->get('/setup/qualsiasi')->assertNotFound();
        $this->post('/setup/qualsiasi', [])->assertNotFound();
    }

    #[Test]
    public function un_token_sbagliato_riceve_404(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $this->get('/setup/token-sbagliato')->assertNotFound();
    }

    #[Test]
    public function con_il_token_corretto_si_crea_il_primo_super_admin(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $this->get('/setup/'.$this->token)->assertOk()->assertSee('Crea il Super Admin');

        $this->post('/setup/'.$this->token, [
            'first_name' => 'Giovanni',
            'last_name' => 'De Rosa',
            'email' => 'capo@azienda.it',
            'password' => 'PasswordSicura1',
            'password_confirmation' => 'PasswordSicura1',
        ])->assertRedirect(route('login'));

        $admin = User::firstWhere('email', 'capo@azienda.it');

        $this->assertSame(Role::ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->must_change_password);
        $this->assertDatabaseHas('audit_logs', ['action' => 'setup.admin_created']);

        // L'account creato entra davvero.
        $this->post(route('login'), ['email' => 'capo@azienda.it', 'password' => 'PasswordSicura1'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function dopo_il_primo_super_admin_la_pagina_sparisce(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $this->post('/setup/'.$this->token, [
            'first_name' => 'Giovanni',
            'last_name' => 'De Rosa',
            'email' => 'capo@azienda.it',
            'password' => 'PasswordSicura1',
            'password_confirmation' => 'PasswordSicura1',
        ])->assertRedirect(route('login'));

        // La porta si è chiusa da sola: nessuno può creare un secondo admin da qui.
        $this->get('/setup/'.$this->token)->assertNotFound();
        $this->post('/setup/'.$this->token, [
            'first_name' => 'Intruso',
            'last_name' => 'Anonimo',
            'email' => 'intruso@azienda.it',
            'password' => 'PasswordSicura1',
            'password_confirmation' => 'PasswordSicura1',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@azienda.it']);
    }

    #[Test]
    public function un_super_admin_disattivato_riapre_la_pagina(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $admin = $this->admin();
        $this->get('/setup/'.$this->token)->assertNotFound();

        // Se l'unico Super Admin è disattivato, l'applicazione sarebbe inaccessibile:
        // la pagina torna disponibile come via di rientro.
        $admin->forceFill(['is_active' => false])->save();

        $this->get('/setup/'.$this->token)->assertOk();
    }

    #[Test]
    public function gli_altri_ruoli_non_contano_come_super_admin(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $this->buyer();
        $this->tecnico();

        $this->get('/setup/'.$this->token)->assertOk();
    }

    #[Test]
    public function la_password_debole_viene_rifiutata(): void
    {
        config(['pescheria.setup_token' => $this->token]);

        $this->post('/setup/'.$this->token, [
            'first_name' => 'Giovanni',
            'last_name' => 'De Rosa',
            'email' => 'capo@azienda.it',
            'password' => 'soltantolettere',
            'password_confirmation' => 'soltantolettere',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'capo@azienda.it']);
    }
}
