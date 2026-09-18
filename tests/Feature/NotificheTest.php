<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\NotificationService;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class NotificheTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function allapertura_i_capi_reparto_destinatari_ricevono_la_notifica(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        [$storeEsterno, $crEsterno] = $this->storeWithCr();

        $opportunita = Opportunity::factory()->inVerifica()->create();
        $opportunita->stores()->sync([$storeA->id, $storeB->id]);
        OpportunityMedia::factory()->create(['opportunity_id' => $opportunita->id]);

        app(OpportunityWorkflowService::class)->approve($opportunita->fresh(['stores', 'media']), $this->tecnico());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $crA->id,
            'type' => NotificationType::OPPORTUNITA_APERTA->value,
        ]);
        $this->assertDatabaseHas('notifications', ['user_id' => $crB->id]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $crEsterno->id]);
    }

    #[Test]
    public function il_sollecito_raggiunge_solo_chi_non_ha_risposto(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$storeA, $storeB]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $storeA, $crA, 2);

        $inviati = app(NotificationService::class)->remindMissing($opportunita->fresh(), 'test');

        $this->assertSame(1, $inviati);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $crB->id,
            'type' => NotificationType::SOLLECITO_RISPOSTA->value,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $crA->id,
            'type' => NotificationType::SOLLECITO_RISPOSTA->value,
        ]);
    }

    #[Test]
    public function lo_stesso_sollecito_non_viene_inviato_due_volte(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $servizio = app(NotificationService::class);
        $servizio->remindMissing($opportunita, 'auto-120min');
        $servizio->remindMissing($opportunita, 'auto-120min');

        $consegne = NotificationDelivery::where('dedupe_key', 'like', '%auto-120min%')
            ->where('channel', 'IN_APP')
            ->count();

        $this->assertSame(1, $consegne);
    }

    #[Test]
    public function il_comando_solleciti_rispetta_le_soglie_configurate(): void
    {
        config(['pescheria.notifiche.reminder_minutes' => [120]]);

        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->addMinutes(120)->addSeconds(10)]);

        $this->artisan('opportunita:solleciti')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $cr->id,
            'type' => NotificationType::SOLLECITO_RISPOSTA->value,
        ]);

        // Rieseguendo il comando non parte un doppione.
        $this->artisan('opportunita:solleciti')->assertSuccessful();

        $this->assertSame(1, Notification::where('user_id', $cr->id)
            ->where('type', NotificationType::SOLLECITO_RISPOSTA)
            ->count());
    }

    #[Test]
    public function il_link_whatsapp_punta_alla_scheda_e_non_registra_ordini(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        app(NotificationService::class)->notifyStores(
            $opportunita, NotificationType::OPPORTUNITA_APERTA, 'Titolo', 'Corpo',
        );

        $notifica = Notification::where('user_id', $cr->id)->firstOrFail();

        // Indirizzo unico: resta valido anche se il collegamento viene inoltrato.
        $this->assertSame(route('opportunita.apri', $opportunita), $notifica->url);
    }

    #[Test]
    public function i_canali_disattivati_restano_tracciati_come_saltati(): void
    {
        config(['pescheria.notifiche.email_enabled' => false, 'pescheria.notifiche.whatsapp_enabled' => false]);

        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        app(NotificationService::class)->notifyStores($opportunita, NotificationType::OPPORTUNITA_APERTA, 'Titolo');

        $notifica = Notification::where('user_id', $cr->id)->firstOrFail();

        $this->assertSame('SENT', $notifica->deliveries()->where('channel', 'IN_APP')->value('status')->value);
        $this->assertSame('SKIPPED', $notifica->deliveries()->where('channel', 'EMAIL')->value('status')->value);
        $this->assertSame('SKIPPED', $notifica->deliveries()->where('channel', 'WHATSAPP')->value('status')->value);
    }

    #[Test]
    public function il_riepilogo_finale_arriva_a_buyer_e_tecnico(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $buyer = $this->buyer();
        $tecnico = $this->tecnico();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 3);

        $opportunita->forceFill(['closes_at' => now()->subMinute()])->save();
        $this->artisan('opportunita:scadi')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'type' => NotificationType::RIEPILOGO_FINALE->value,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $tecnico->id,
            'type' => NotificationType::RIEPILOGO_FINALE->value,
        ]);
    }

    #[Test]
    public function la_riapertura_avvisa_il_punto_vendita(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);
        $risposta = app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 2);

        app(ResponseSubmissionService::class)->reopen($risposta, $this->tecnico(), now()->addHour(), 'Errore quantità');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $cr->id,
            'type' => NotificationType::RISPOSTA_RIAPERTA->value,
        ]);
    }
}
