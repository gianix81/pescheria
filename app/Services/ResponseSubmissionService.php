<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\ResponseStatus;
use App\Exceptions\DomainException;
use App\Exceptions\InvalidQuantityException;
use App\Exceptions\ResponseWindowClosedException;
use App\Models\Opportunity;
use App\Models\Response;
use App\Models\ResponseRevision;
use App\Models\Store;
use App\Models\User;
use App\Support\Format;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Tutte le scritture sulle risposte dei Capi Reparto passano da qui.
 *
 * Invarianti garantite:
 *  - la finestra temporale è verificata sull'orologio del server, mai sul browser;
 *  - lotto minimo e multiplo di ordinazione sono sempre applicati;
 *  - lo stock limitato è impegnato solo all'invio definitivo, dentro la transazione
 *    che blocca la riga dell'opportunità;
 *  - ogni passaggio scrive una revisione con valore precedente e nuovo.
 */
class ResponseSubmissionService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    public function findOrCreate(Opportunity $opportunity, Store $store): Response
    {
        return $opportunity->responses()->firstOrCreate(
            ['store_id' => $store->id],
            ['status' => ResponseStatus::NON_COMPILATA, 'packages' => 0, 'kg' => 0],
        );
    }

    // ------------------------------------------------------------------ bozza

    /** Salvataggio bozza: non impegna stock e non vale come ordine. */
    public function saveDraft(Opportunity $opportunity, Store $store, User $user, ?int $packages, ?string $refusalReason = null): Response
    {
        $this->assertWindowOpen($opportunity, $this->findOrCreate($opportunity, $store));

        if ($packages !== null && $packages > 0) {
            $this->assertValidQuantity($opportunity, $packages);
        }

        return DB::transaction(function () use ($opportunity, $store, $user, $packages, $refusalReason) {
            $response = $this->findOrCreate($opportunity, $store);
            $from = ['status' => $response->status, 'packages' => (int) $response->packages];

            $response->fill([
                'status' => ResponseStatus::BOZZA,
                'packages' => (int) $packages,
                'kg' => $this->kgFor($opportunity, (int) $packages),
                'refusal_reason' => $refusalReason,
                'last_actor_id' => $user->id,
            ])->save();

            $this->recordRevision($response, $user, 'BOZZA_SALVATA', $from);

            return $response;
        });
    }

    // ------------------------------------------------------------------ invio

    /** Invio definitivo di un acquisto: impegna lo stock in transazione. */
    public function submitPurchase(Opportunity $opportunity, Store $store, User $user, int $packages): Response
    {
        if ($packages <= 0) {
            throw new InvalidQuantityException('Per acquistare indica un numero di colli maggiore di zero.');
        }

        $this->assertValidQuantity($opportunity, $packages);

        return DB::transaction(function () use ($opportunity, $store, $user, $packages) {
            // Lock della riga: due CR che inviano nello stesso istante si serializzano qui.
            $locked = $this->availability->lock($opportunity);

            $response = Response::where('opportunity_id', $opportunity->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->first()
                ?? $this->findOrCreate($opportunity, $store);

            $this->assertWindowOpen($locked, $response);

            $from = ['status' => $response->status, 'packages' => (int) $response->packages];

            $this->availability->applyCommitment($locked, $response, $packages);

            $response->fill([
                'status' => ResponseStatus::INVIATA_ACQUISTO,
                'packages' => $packages,
                'kg' => $this->kgFor($locked, $packages),
                'refusal_reason' => null,
                'submitted_at' => now(),
                'last_actor_id' => $user->id,
            ])->save();

            $this->recordRevision($response, $user, 'INVIO_ACQUISTO', $from);

            $this->audit->log('response.submitted', $response, [
                'opportunita' => $opportunity->reference,
                'punto_vendita' => $store->code,
                'colli' => $packages,
            ], $user);

            return $response;
        });
    }

    /** Rifiuto esplicito: zero colli, ma è una risposta, non un'assenza. */
    public function submitRefusal(Opportunity $opportunity, Store $store, User $user, ?string $reason = null): Response
    {
        $requiresReason = $opportunity->requires_refusal_reason || config('pescheria.require_refusal_reason');

        if ($requiresReason && trim((string) $reason) === '') {
            throw new DomainException('La motivazione del rifiuto è obbligatoria.');
        }

        return DB::transaction(function () use ($opportunity, $store, $user, $reason) {
            $locked = $this->availability->lock($opportunity);

            $response = Response::where('opportunity_id', $opportunity->id)
                ->where('store_id', $store->id)
                ->lockForUpdate()
                ->first()
                ?? $this->findOrCreate($opportunity, $store);

            $this->assertWindowOpen($locked, $response);

            $from = ['status' => $response->status, 'packages' => (int) $response->packages];

            // Il rifiuto libera immediatamente l'eventuale quantità già impegnata.
            $this->availability->release($locked, $response);

            $response->fill([
                'status' => ResponseStatus::INVIATA_RIFIUTO,
                'packages' => 0,
                'kg' => 0,
                'refusal_reason' => $reason,
                'submitted_at' => now(),
                'last_actor_id' => $user->id,
            ])->save();

            $this->recordRevision($response, $user, 'INVIO_RIFIUTO', $from, $reason);

            $this->audit->log('response.refused', $response, [
                'opportunita' => $opportunity->reference,
                'punto_vendita' => $store->code,
            ], $user);

            return $response;
        });
    }

    // ------------------------------------------------------------------ eccezioni

    /** Riapertura della singola risposta da parte del Tecnico, entro una nuova scadenza. */
    public function reopen(Response $response, User $tecnico, Carbon $until, string $reason): Response
    {
        if (trim($reason) === '') {
            throw new DomainException('La motivazione della riapertura è obbligatoria.');
        }

        if ($until->lessThanOrEqualTo(now())) {
            throw new DomainException('La nuova scadenza deve essere futura.');
        }

        return DB::transaction(function () use ($response, $tecnico, $until, $reason) {
            $from = ['status' => $response->status, 'packages' => (int) $response->packages];

            $response->fill([
                'status' => ResponseStatus::RIAPERTA,
                'reopened_until' => $until,
                'reopened_by' => $tecnico->id,
                'reopen_reason' => $reason,
            ])->save();

            $this->recordRevision($response, $tecnico, 'RIAPERTURA', $from, $reason);

            $this->audit->log('response.reopened', $response, [
                'opportunita' => $response->opportunity->reference,
                'punto_vendita' => $response->store->code,
                'nuova_scadenza' => $until->toIso8601String(),
                'motivazione' => $reason,
            ], $tecnico);

            $this->notifications->notifyStoreUsers(
                $response->opportunity,
                $response->store,
                NotificationType::RISPOSTA_RIAPERTA,
                'Risposta riaperta: '.$response->opportunity->title,
                'Puoi aggiornare la risposta entro il '.Format::dateTime($until).'. Motivo: '.$reason,
            );

            return $response;
        });
    }

    /**
     * Correzione eccezionale del Buyer dopo la scadenza (capitolato §3, ruolo Buyer).
     * Non sostituisce la risposta del CR: resta tracciata come intervento del Buyer.
     */
    public function overrideAfterDeadline(Response $response, User $buyer, int $packages, string $reason): Response
    {
        if (trim($reason) === '') {
            throw new DomainException('La motivazione della correzione è obbligatoria.');
        }

        if ($packages < 0) {
            throw new InvalidQuantityException('La quantità non può essere negativa.');
        }

        $opportunity = $response->opportunity;

        if ($packages > 0) {
            $this->assertValidQuantity($opportunity, $packages);
        }

        return DB::transaction(function () use ($response, $buyer, $packages, $reason, $opportunity) {
            $locked = $this->availability->lock($opportunity);
            $from = ['status' => $response->status, 'packages' => (int) $response->packages];

            $this->availability->applyCommitment($locked, $response, $packages);

            $response->fill([
                'status' => $packages > 0 ? ResponseStatus::INVIATA_ACQUISTO : ResponseStatus::INVIATA_RIFIUTO,
                'packages' => $packages,
                'kg' => $this->kgFor($locked, $packages),
                'last_actor_id' => $buyer->id,
            ])->save();

            $this->recordRevision($response, $buyer, 'CORREZIONE_BUYER', $from, $reason);

            $this->audit->log('response.override', $response, [
                'opportunita' => $opportunity->reference,
                'punto_vendita' => $response->store->code,
                'colli_precedenti' => $from['packages'],
                'colli_nuovi' => $packages,
                'motivazione' => $reason,
            ], $buyer);

            return $response;
        });
    }

    // ------------------------------------------------------------------ regole

    /** @throws ResponseWindowClosedException */
    public function assertWindowOpen(Opportunity $opportunity, Response $response): void
    {
        if ($response->hasActiveReopening()) {
            return;     // riapertura concessa dal Tecnico
        }

        if (! $opportunity->isAcceptingResponses()) {
            throw new ResponseWindowClosedException(
                $opportunity->isExpired()
                    ? 'I termini per rispondere sono scaduti il '.Format::dateTime($opportunity->closes_at).'.'
                    : 'L\'opportunità non è aperta alle risposte.',
            );
        }
    }

    /** @throws InvalidQuantityException */
    public function assertValidQuantity(Opportunity $opportunity, int $packages): void
    {
        $min = max(1, (int) $opportunity->min_lot);
        $multiple = max(1, (int) $opportunity->order_multiple);

        if ($packages < $min) {
            throw new InvalidQuantityException("Il lotto minimo è di {$min} colli.");
        }

        if (($packages - $min) % $multiple !== 0) {
            throw new InvalidQuantityException("La quantità deve rispettare il multiplo di {$multiple} colli a partire da {$min}.");
        }
    }

    public function kgFor(Opportunity $opportunity, int $packages): float
    {
        return round($packages * (float) $opportunity->kg_per_package, 3);
    }

    private function recordRevision(Response $response, User $user, string $action, array $from, ?string $reason = null): void
    {
        ResponseRevision::create([
            'response_id' => $response->id,
            'user_id' => $user->id,
            'action' => $action,
            'from_status' => $from['status'] instanceof ResponseStatus ? $from['status']->value : $from['status'],
            'to_status' => $response->status->value,
            'from_packages' => $from['packages'],
            'to_packages' => (int) $response->packages,
            'reason' => $reason,
            'ip_address' => $this->safeIp(),
            'created_at' => now(),
        ]);
    }

    private function safeIp(): ?string
    {
        try {
            return Request::ip();
        } catch (\Throwable) {
            return null;
        }
    }
}
