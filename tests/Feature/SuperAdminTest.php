<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Tecnico\Anagrafiche\Utenti;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Il Super Admin gestisce i profili e accede a tutta l'applicazione.
 * Le azioni irreversibili restano sue soltanto, e con dei paracadute.
 */
class SuperAdminTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function accede_a_tutte_le_sezioni(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $admin = $this->admin();

        foreach ([
            route('tecnico.anagrafiche.utenti'),
            route('tecnico.anagrafiche.punti-vendita'),
            route('tecnico.anagrafiche.prodotti'),
            route('tecnico.dashboard'),
            route('tecnico.monitor'),
            route('tecnico.audit'),
            route('buyer.dashboard'),
            route('buyer.opportunita.create'),
            route('buyer.ordini'),
            route('opportunita.index'),
            route('export.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    #[Test]
    public function dopo_il_login_atterra_sulla_gestione_profili(): void
    {
        $this->actingAs($this->admin())->get('/')->assertRedirect(route('tecnico.anagrafiche.utenti'));
    }

    #[Test]
    public function crea_un_profilo_con_qualsiasi_ruolo(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Utenti::class)
            ->set('form.first_name', 'Nuova')
            ->set('form.last_name', 'Collega')
            ->set('form.email', 'nuova@pescheria.local')
            ->set('form.role', 'TECNICO')
            ->set('password', 'PasswordSicura1')
            ->call('salva')
            ->assertHasNoErrors();

        $creata = User::firstWhere('email', 'nuova@pescheria.local');

        $this->assertSame(Role::TECNICO, $creata->role);
        $this->assertTrue($creata->must_change_password);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created']);
    }

    #[Test]
    public function elimina_e_ripristina_un_profilo(): void
    {
        $admin = $this->admin();
        [$store, $cr] = $this->storeWithCr();

        Livewire::actingAs($admin)
            ->test(Utenti::class)
            ->call('chiediEliminazione', $cr->id)
            ->call('elimina')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('users', ['id' => $cr->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deleted']);

        // Un profilo eliminato non entra più. Serve una sessione ospite: le rotte
        // di accesso sono dietro il middleware "guest".
        auth()->logout();

        $this->post(route('login'), ['email' => $cr->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        Livewire::actingAs($admin)
            ->test(Utenti::class)
            ->call('ripristina', $cr->id);

        $this->assertNotSoftDeleted('users', ['id' => $cr->id]);
    }

    #[Test]
    public function reimposta_la_password_mostrandola_una_sola_volta(): void
    {
        $admin = $this->admin();
        [$store, $cr] = $this->storeWithCr();

        $componente = Livewire::actingAs($admin)
            ->test(Utenti::class)
            ->call('reimpostaPassword', $cr->id);

        $nuova = $componente->get('passwordGenerata');

        $this->assertNotEmpty($nuova);
        $this->assertTrue(Hash::check($nuova, $cr->fresh()->password));
        $this->assertTrue($cr->fresh()->must_change_password);

        // La password non deve finire nell'audit log.
        $voce = AuditLog::where('action', 'user.password_reset')->firstOrFail();
        $this->assertStringNotContainsString($nuova, json_encode($voce->payload));
    }

    #[Test]
    public function disattiva_un_profilo_impedendone_laccesso(): void
    {
        $admin = $this->admin();
        [$store, $cr] = $this->storeWithCr();

        Livewire::actingAs($admin)->test(Utenti::class)->call('attivaDisattiva', $cr->id);

        $this->assertFalse($cr->fresh()->is_active);

        auth()->logout();

        $this->post(route('login'), ['email' => $cr->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function non_puo_eliminare_se_stesso(): void
    {
        $admin = $this->admin();
        $this->admin();     // un secondo admin, così non scatta l'altro paracadute

        Livewire::actingAs($admin)
            ->test(Utenti::class)
            ->call('chiediEliminazione', $admin->id)
            ->call('elimina')
            ->assertHasErrors('eliminazione');

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    #[Test]
    public function lultimo_super_admin_attivo_non_puo_essere_rimosso(): void
    {
        $unico = $this->admin();
        $altro = $this->admin();

        // Eliminato il secondo, resta un solo Super Admin attivo.
        Livewire::actingAs($unico)->test(Utenti::class)
            ->call('chiediEliminazione', $altro->id)
            ->call('elimina')
            ->assertHasNoErrors();

        // Ora nemmeno disattivarlo o declassarlo è consentito.
        Livewire::actingAs($unico)->test(Utenti::class)
            ->call('attivaDisattiva', $unico->id)
            ->assertHasErrors('eliminazione');

        $this->assertTrue($unico->fresh()->is_active);

        Livewire::actingAs($unico)->test(Utenti::class)
            ->call('modifica', $unico->id)
            ->set('form.role', 'BUYER')
            ->call('salva')
            ->assertHasErrors('eliminazione');

        $this->assertSame(Role::ADMIN, $unico->fresh()->role);
    }

    #[Test]
    public function il_tecnico_gestisce_i_profili_ma_non_le_azioni_distruttive(): void
    {
        $tecnico = $this->tecnico();
        [$store, $cr] = $this->storeWithCr();

        // Creare e modificare: sì.
        Livewire::actingAs($tecnico)
            ->test(Utenti::class)
            ->set('form.first_name', 'Altro')
            ->set('form.last_name', 'Capo')
            ->set('form.email', 'altro@pescheria.local')
            ->set('form.role', 'CAPO_REPARTO')
            ->set('form.store_id', $store->id)
            ->set('password', 'PasswordSicura1')
            ->call('salva')
            ->assertHasNoErrors();

        // Eliminare e reimpostare password: no.
        Livewire::actingAs($tecnico)->test(Utenti::class)
            ->call('chiediEliminazione', $cr->id)
            ->assertForbidden();

        Livewire::actingAs($tecnico)->test(Utenti::class)
            ->call('reimpostaPassword', $cr->id)
            ->assertForbidden();
    }

    #[Test]
    public function solo_un_super_admin_puo_nominare_altri_super_admin(): void
    {
        Livewire::actingAs($this->tecnico())
            ->test(Utenti::class)
            ->set('form.first_name', 'Aspirante')
            ->set('form.last_name', 'Admin')
            ->set('form.email', 'aspirante@pescheria.local')
            ->set('form.role', 'ADMIN')
            ->set('password', 'PasswordSicura1')
            ->call('salva')
            ->assertHasErrors('form.role');

        $this->assertDatabaseMissing('users', ['email' => 'aspirante@pescheria.local']);
    }

    #[Test]
    public function un_capo_reparto_non_accede_alla_gestione_profili(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get(route('tecnico.anagrafiche.utenti'))->assertForbidden();
    }

    #[Test]
    public function il_comando_crea_un_super_admin_utilizzabile(): void
    {
        $this->artisan('pescheria:admin', [
            '--email' => 'capo@azienda.it',
            '--password' => 'PasswordSicura1',
            '--nome' => 'Mario',
            '--cognome' => 'Rossi',
        ])->assertSuccessful();

        $admin = User::firstWhere('email', 'capo@azienda.it');

        $this->assertSame(Role::ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->must_change_password);

        $this->post(route('login'), ['email' => 'capo@azienda.it', 'password' => 'PasswordSicura1'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function il_comando_ripristina_un_profilo_eliminato_o_disattivato(): void
    {
        $utente = User::factory()->create([
            'email' => 'bloccato@azienda.it',
            'role' => Role::BUYER,
            'is_active' => false,
        ]);
        $utente->delete();

        $this->artisan('pescheria:admin', [
            '--email' => 'bloccato@azienda.it',
            '--password' => 'PasswordSicura1',
        ])->assertSuccessful();

        $ripristinato = User::firstWhere('email', 'bloccato@azienda.it');

        $this->assertNotNull($ripristinato);
        $this->assertTrue($ripristinato->is_active);
        $this->assertSame(Role::ADMIN, $ripristinato->role);
    }

    #[Test]
    public function il_comando_rifiuta_una_password_debole(): void
    {
        $this->artisan('pescheria:admin', [
            '--email' => 'debole@azienda.it',
            '--password' => 'corta',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'debole@azienda.it']);
    }

    #[Test]
    public function il_comando_di_stato_segnala_lassenza_di_utenti(): void
    {
        User::query()->forceDelete();

        $this->artisan('pescheria:stato')
            ->expectsOutputToContain('Nessun utente nel database')
            ->assertSuccessful();
    }

    #[Test]
    public function il_comando_di_stato_spiega_perche_un_account_non_entra(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $cr->forceFill(['is_active' => false])->save();

        $this->artisan('pescheria:stato', ['--email' => $cr->email])
            ->expectsOutputToContain('non è utilizzabile')
            ->assertSuccessful();
    }
}
