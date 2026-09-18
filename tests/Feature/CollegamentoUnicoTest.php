<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Services\NotificationService;
use App\Services\ResponseSubmissionService;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Un messaggio WhatsApp finisce in un gruppo misto: Buyer, Tecnici e capi
 * reparto aprono lo stesso collegamento. Mettere nel testo l'indirizzo di una
 * sola vista condannava tutti gli altri a un 403.
 */
class CollegamentoUnicoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function ogni_ruolo_viene_portato_alla_propria_schermata(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);
        $url = route('opportunita.apri', $opportunita);

        $this->actingAs($cr)->get($url)
            ->assertRedirect(route('cr.opportunita.show', $opportunita));

        $this->actingAs($this->buyer())->get($url)
            ->assertRedirect(route('opportunita.show', $opportunita));

        $this->actingAs($this->tecnico())->get($url)
            ->assertRedirect(route('opportunita.show', $opportunita));

        $this->actingAs($this->admin())->get($url)
            ->assertRedirect(route('opportunita.show', $opportunita));
    }

    #[Test]
    public function seguendo_il_collegamento_si_arriva_davvero_alla_pagina(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        // Nessun 403 lungo il percorso, per nessuno dei ruoli.
        $this->actingAs($cr)->get(route('opportunita.apri', $opportunita))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->followingRedirects()
            ->actingAs($cr)->get(route('opportunita.apri', $opportunita))
            ->assertOk()->assertSee($opportunita->article_code);

        $this->followingRedirects()
            ->actingAs($this->tecnico())->get(route('opportunita.apri', $opportunita))
            ->assertOk()->assertSee($opportunita->reference);
    }

    #[Test]
    public function chi_non_ha_ancora_fatto_accesso_passa_dal_login_e_poi_arriva(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->get(route('opportunita.apri', $opportunita))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $cr->email, 'password' => 'password'])
            ->assertRedirect(route('opportunita.apri', $opportunita));
    }

    #[Test]
    public function un_capo_reparto_non_destinatario_riceve_un_messaggio_comprensibile(): void
    {
        [$destinatario] = $this->storeWithCr('PV951');
        [$estraneo, $crEstraneo] = $this->storeWithCr('PV952');
        $opportunita = $this->openOpportunity([$destinatario]);

        $risposta = $this->actingAs($crEstraneo)->get(route('opportunita.apri', $opportunita));

        $risposta->assertForbidden();
        // Non più il generico "non hai i permessi per questa sezione".
        $this->assertStringContainsString(
            'non è destinata al tuo punto vendita',
            $risposta->exception?->getMessage() ?? '',
        );
    }

    #[Test]
    public function tutti_i_messaggi_whatsapp_usano_lindirizzo_unico(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);
        $risposta = app(ResponseSubmissionService::class)
            ->submitPurchase($opportunita, $store, $cr, 2);

        $unico = route('opportunita.apri', $opportunita);

        foreach ([
            'apertura' => WhatsApp::perApertura($opportunita->fresh()),
            'verifica' => WhatsApp::perVerifica($opportunita),
            'sollecito' => WhatsApp::perSollecito($opportunita),
            'aggiornamento' => WhatsApp::perAggiornamento($opportunita),
            'ripubblicazione' => WhatsApp::perRipubblicazione($opportunita),
            'risposta' => WhatsApp::perRisposta($risposta->fresh(['opportunity', 'store'])),
        ] as $nome => $testo) {
            $this->assertStringContainsString($unico, $testo, "Il messaggio «{$nome}» non usa l'indirizzo unico.");
            $this->assertStringNotContainsString(route('cr.opportunita.show', $opportunita), $testo, $nome);
            $this->assertStringNotContainsString(route('opportunita.show', $opportunita), $testo, $nome);
        }
    }

    #[Test]
    public function anche_le_notifiche_in_app_usano_lindirizzo_unico(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        app(NotificationService::class)->notifyStores(
            $opportunita,
            NotificationType::OPPORTUNITA_APERTA,
            'Titolo',
        );

        $notifica = Notification::where('user_id', $cr->id)->firstOrFail();

        $this->assertSame(route('opportunita.apri', $opportunita), $notifica->url);

        // E aprendola si arriva alla propria schermata.
        $this->actingAs($cr)->get(route('notifiche.open', $notifica))
            ->assertRedirect(route('opportunita.apri', $opportunita));
    }

    #[Test]
    public function il_collegamento_e_corto_abbastanza_per_un_messaggio(): void
    {
        $opportunita = Opportunity::factory()->create();

        $this->assertStringContainsString('/o/'.$opportunita->id, route('opportunita.apri', $opportunita));
    }
}
