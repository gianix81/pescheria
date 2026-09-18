<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Enums\ResponseStatus;
use App\Enums\Role;
use App\Jobs\DeliverNotification;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\Opportunity;
use App\Models\Response;
use App\Models\Store;
use App\Models\User;
use App\Support\Format;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Notifiche in-app (obbligatorie) più canali opzionali email/WhatsApp.
 *
 * WhatsApp non è mai la fonte dell'ordine: il messaggio contiene solo un deep link
 * alla scheda dell'opportunità, che richiede login per essere compilata.
 *
 * L'idempotenza è garantita da `notification_deliveries.dedupe_key` (unique):
 * rieseguire un job non produce doppioni.
 */
class NotificationService
{
    /** @var NotificationChannel[] */
    private array $optionalChannels = [NotificationChannel::EMAIL, NotificationChannel::WHATSAPP];

    public function notifyUser(
        ?User $user,
        ?Opportunity $opportunity,
        NotificationType $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        string $dedupeSuffix = '',
        ?Store $store = null,
    ): ?Notification {
        if (! $user || ! $user->is_active) {
            return null;
        }

        $url ??= $opportunity ? $this->deepLink($opportunity, $user) : null;

        // Idempotenza: con un suffisso esplicito (es. "auto-120min") la stessa notifica
        // non viene ricreata, nemmeno se il job viene rieseguito.
        $chiave = $dedupeSuffix !== '' ? $dedupeSuffix : 'n'.Str::random(12);

        if ($dedupeSuffix !== '' && NotificationDelivery::where('dedupe_key', $this->dedupeKey($type, NotificationChannel::IN_APP, $user->id, $opportunity?->id, $chiave))->exists()) {
            return null;
        }

        return DB::transaction(function () use ($user, $opportunity, $type, $title, $body, $url, $chiave, $store) {
            $notification = Notification::create([
                'type' => $type,
                'user_id' => $user->id,
                'opportunity_id' => $opportunity?->id,
                'store_id' => $store?->id,
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ]);

            // Canale in-app: consegnato subito, è la fonte obbligatoria.
            $this->makeDelivery($notification, NotificationChannel::IN_APP, $chiave, DeliveryStatus::SENT);

            foreach ($this->optionalChannels as $channel) {
                if (! $this->channelEnabled($channel)) {
                    $this->makeDelivery($notification, $channel, $chiave, DeliveryStatus::SKIPPED);

                    continue;
                }

                $delivery = $this->makeDelivery($notification, $channel, $chiave, DeliveryStatus::PENDING);

                if ($delivery) {
                    DeliverNotification::dispatch($delivery->id)->afterCommit();
                }
            }

            return $notification;
        });
    }

    public function notifyRole(Role $role, ?Opportunity $opportunity, NotificationType $type, string $title, ?string $body = null, string $dedupeSuffix = ''): void
    {
        User::where('role', $role)->where('is_active', true)->get()
            ->each(fn (User $user) => $this->notifyUser($user, $opportunity, $type, $title, $body, null, $dedupeSuffix));
    }

    /** Notifica tutti i CR dei punti vendita destinatari. */
    public function notifyStores(Opportunity $opportunity, NotificationType $type, string $title, ?string $body = null, string $dedupeSuffix = ''): void
    {
        $this->capiRepartoOf($opportunity)->each(
            fn (User $user) => $this->notifyUser($user, $opportunity, $type, $title, $body, null, $dedupeSuffix, $user->store),
        );
    }

    public function notifyStoreUsers(Opportunity $opportunity, Store $store, NotificationType $type, string $title, ?string $body = null, string $dedupeSuffix = ''): void
    {
        User::where('role', Role::CAPO_REPARTO)
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $user) => $this->notifyUser($user, $opportunity, $type, $title, $body, null, $dedupeSuffix, $store));
    }

    /**
     * Sollecito ai CR che non hanno ancora inviato la risposta.
     * `$dedupeSuffix` (es. "120min") impedisce l'invio doppio dello stesso promemoria.
     *
     * @return int numero di solleciti creati
     */
    public function remindMissing(Opportunity $opportunity, string $dedupeSuffix = 'manuale', ?array $storeIds = null): int
    {
        $missingStoreIds = $this->missingStoreIds($opportunity);

        if ($storeIds !== null) {
            $missingStoreIds = array_values(array_intersect($missingStoreIds, $storeIds));
        }

        if ($missingStoreIds === []) {
            return 0;
        }

        $users = User::where('role', Role::CAPO_REPARTO)
            ->whereIn('store_id', $missingStoreIds)
            ->where('is_active', true)
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            $notification = $this->notifyUser(
                $user,
                $opportunity,
                NotificationType::SOLLECITO_RISPOSTA,
                'Sollecito: '.$opportunity->title,
                'Scadenza '.Format::dateTime($opportunity->closes_at).'. Indica se acquisti oppure no.',
                null,
                $dedupeSuffix,
                $user->store,
            );

            if ($notification) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Un punto vendita ha inviato la propria risposta: lo sanno subito il Buyer
     * che ha creato l'opportunità e i Tecnici che la sorvegliano.
     */
    public function notifyResponseSubmitted(Response $response): void
    {
        $opportunity = $response->opportunity;
        $store = $response->store;

        $titolo = $response->packages > 0
            ? $store->code.' ordina '.$response->packages.' colli'
            : $store->code.' non acquista';

        $stat = $opportunity->completionStats();
        $corpo = $opportunity->title.' — risposte '.$stat['inviate'].'/'.$stat['destinatari']
            .', totale '.$opportunity->totalPackagesOrdered().' colli.';

        $chiave = 'risposta-'.$response->id.'-'.$response->updated_at?->timestamp;

        $this->notifyUser($opportunity->creator, $opportunity, NotificationType::RISPOSTA_INVIATA, $titolo, $corpo, null, $chiave, $store);
        $this->notifyRole(Role::TECNICO, $opportunity, NotificationType::RISPOSTA_INVIATA, $titolo, $corpo, $chiave);
    }

    /** Riepilogo finale a Buyer e Tecnici alla chiusura/scadenza. */
    public function notifyFinalSummary(Opportunity $opportunity): void
    {
        $stats = $opportunity->completionStats();
        $body = sprintf(
            '%d colli per %s kg — risposte %d/%d, mancanti %d.',
            $opportunity->totalPackagesOrdered(),
            Format::decimal($opportunity->totalKgOrdered(), 2),
            $stats['inviate'],
            $stats['destinatari'],
            $stats['mancanti'],
        );

        $this->notifyUser(
            $opportunity->creator,
            $opportunity,
            NotificationType::RIEPILOGO_FINALE,
            'Riepilogo: '.$opportunity->title,
            $body,
            null,
            'riepilogo',
        );

        $this->notifyRole(Role::TECNICO, $opportunity, NotificationType::RIEPILOGO_FINALE, 'Riepilogo: '.$opportunity->title, $body, 'riepilogo');
    }

    /** @return int[] id dei punti vendita destinatari senza risposta inviata */
    public function missingStoreIds(Opportunity $opportunity): array
    {
        $recipients = $opportunity->stores()->pluck('stores.id')->all();

        $answered = $opportunity->responses()
            ->whereIn('status', [ResponseStatus::INVIATA_ACQUISTO, ResponseStatus::INVIATA_RIFIUTO])
            ->pluck('store_id')
            ->all();

        return array_values(array_diff($recipients, $answered));
    }

    /** @return Collection<int, User> */
    public function capiRepartoOf(Opportunity $opportunity): Collection
    {
        return User::where('role', Role::CAPO_REPARTO)
            ->whereIn('store_id', $opportunity->stores()->pluck('stores.id'))
            ->where('is_active', true)
            ->with('store')
            ->get();
    }

    /** Link profondo alla scheda: apre l'app, non registra ordini. */
    public function deepLink(Opportunity $opportunity, User $user): string
    {
        return $user->isCapoReparto()
            ? route('cr.opportunita.show', $opportunity)
            : route('opportunita.show', $opportunity);
    }

    private function channelEnabled(NotificationChannel $channel): bool
    {
        return match ($channel) {
            NotificationChannel::EMAIL => (bool) config('pescheria.notifiche.email_enabled'),
            NotificationChannel::WHATSAPP => (bool) config('pescheria.notifiche.whatsapp_enabled'),
            NotificationChannel::IN_APP => true,
        };
    }

    private function makeDelivery(Notification $notification, NotificationChannel $channel, string $suffix, DeliveryStatus $status): ?NotificationDelivery
    {
        $key = $this->dedupeKey($notification->type, $channel, $notification->user_id, $notification->opportunity_id, $suffix);

        if (NotificationDelivery::where('dedupe_key', $key)->exists()) {
            return null;
        }

        return NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => $channel,
            'status' => $status,
            'sent_at' => $status === DeliveryStatus::SENT ? now() : null,
            'dedupe_key' => $key,
        ]);
    }

    private function dedupeKey(NotificationType $type, NotificationChannel $channel, int $userId, ?int $opportunityId, string $suffix): string
    {
        return substr(implode(':', [
            $type->value,
            $channel->value,
            'u'.$userId,
            'o'.($opportunityId ?? 0),
            $suffix,
        ]), 0, 191);
    }
}
