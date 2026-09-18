<?php

namespace App\Services;

use App\Enums\AvailabilityType;
use App\Enums\NotificationType;
use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Enums\ReviewOutcome;
use App\Enums\Role;
use App\Exceptions\DomainException;
use App\Exceptions\InvalidTransitionException;
use App\Models\Opportunity;
use App\Models\OpportunityReview;
use App\Models\User;
use App\Support\Format;
use Illuminate\Support\Facades\DB;

/**
 * Macchina a stati dell'opportunità (capitolato §6).
 * Nessun altro punto dell'applicazione può cambiare `opportunities.status`.
 */
class OpportunityWorkflowService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
        private readonly AvailabilityService $availability,
    ) {}

    // ------------------------------------------------------------- creazione

    public function createDraft(array $data, User $buyer): Opportunity
    {
        return DB::transaction(function () use ($data, $buyer) {
            $opportunity = new Opportunity($data);
            $opportunity->reference = Opportunity::nextReference();
            $opportunity->created_by = $buyer->id;
            $opportunity->status = OpportunityStatus::BOZZA;
            $opportunity->quick_quantities ??= config('pescheria.quick_quantities');
            $opportunity->recalculatePricing();
            $opportunity->save();

            if (isset($data['store_ids'])) {
                $opportunity->stores()->sync($data['store_ids']);
            }

            $this->audit->log('opportunity.created', $opportunity, [
                'reference' => $opportunity->reference,
                'article_code' => $opportunity->article_code,
            ], $buyer);

            return $opportunity->fresh(['stores', 'media']);
        });
    }

    public function updateDraft(Opportunity $opportunity, array $data, User $user): Opportunity
    {
        if (! $opportunity->status->isEditableByBuyer()) {
            throw new DomainException('L\'opportunità non è modificabile nello stato '.$opportunity->status->label().'.');
        }

        return DB::transaction(function () use ($opportunity, $data, $user) {
            $before = $opportunity->only(array_keys($data));

            $opportunity->fill($data);
            $opportunity->recalculatePricing();
            $opportunity->save();

            if (array_key_exists('store_ids', $data)) {
                $opportunity->stores()->sync($data['store_ids']);
            }

            $this->audit->log('opportunity.updated', $opportunity, [
                'prima' => $before,
                'dopo' => $opportunity->only(array_keys($data)),
            ], $user);

            return $opportunity->fresh(['stores', 'media']);
        });
    }

    /**
     * Modifica di un'opportunità già pubblicata (il Buyer può intervenire in qualsiasi momento).
     * Notifica sempre i destinatari: nessuna modifica silenziosa.
     */
    public function updatePublished(Opportunity $opportunity, array $data, User $user): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $data, $user) {
            $before = $opportunity->only(array_keys($data));

            $opportunity->fill($data);
            $opportunity->recalculatePricing();
            $opportunity->save();

            if (array_key_exists('store_ids', $data)) {
                $opportunity->stores()->sync($data['store_ids']);
            }

            $this->audit->log('opportunity.updated_published', $opportunity, [
                'prima' => $before,
                'dopo' => $opportunity->only(array_keys($data)),
            ], $user);

            $this->notifications->notifyStores(
                $opportunity,
                NotificationType::OPPORTUNITA_MODIFICATA,
                'Opportunità aggiornata: '.$opportunity->title,
                'I dati dell\'opportunità '.$opportunity->reference.' sono cambiati. Verifica prima di confermare.',
            );

            return $opportunity->fresh(['stores', 'media']);
        });
    }

    // ------------------------------------------------------------- verifica

    public function submitForReview(Opportunity $opportunity, User $buyer): Opportunity
    {
        $this->assertReadyForReview($opportunity);
        $this->transition($opportunity, OpportunityStatus::IN_VERIFICA, $buyer, [
            'submitted_at' => now(),
        ]);

        $this->notifications->notifyRole(
            Role::TECNICO,
            $opportunity,
            NotificationType::OPPORTUNITA_IN_VERIFICA,
            'Da verificare: '.$opportunity->title,
            $opportunity->reference.' è in attesa di verifica.',
        );

        return $opportunity;
    }

    public function approve(Opportunity $opportunity, User $tecnico, ?string $notes = null, array $checklist = []): Opportunity
    {
        if ($opportunity->status !== OpportunityStatus::IN_VERIFICA) {
            throw new DomainException('Solo un\'opportunità in verifica può essere approvata.');
        }

        $this->assertPublishable($opportunity);

        // Apertura immediata se la finestra è già iniziata, altrimenti resta programmata.
        $target = $opportunity->opens_at->lessThanOrEqualTo(now())
            ? OpportunityStatus::APERTA
            : OpportunityStatus::PROGRAMMATA;

        DB::transaction(function () use ($opportunity, $tecnico, $notes, $checklist, $target) {
            OpportunityReview::create([
                'opportunity_id' => $opportunity->id,
                'reviewer_id' => $tecnico->id,
                'outcome' => ReviewOutcome::APPROVATA,
                'notes' => $notes,
                'checklist' => $checklist,
            ]);

            $this->transition($opportunity, $target, $tecnico, [
                'reviewed_by' => $tecnico->id,
                'approved_at' => now(),
                'review_notes' => $notes,
                'published_at' => $target === OpportunityStatus::APERTA ? now() : null,
            ]);
        });

        $this->notifications->notifyUser(
            $opportunity->creator,
            $opportunity,
            NotificationType::OPPORTUNITA_APPROVATA,
            'Approvata: '.$opportunity->title,
            'Apertura '.Format::dateTime($opportunity->opens_at).', scadenza '.Format::dateTime($opportunity->closes_at).'.',
        );

        if ($target === OpportunityStatus::APERTA) {
            $this->announceOpening($opportunity);
        }

        return $opportunity;
    }

    public function reject(Opportunity $opportunity, User $tecnico, string $reason): Opportunity
    {
        if ($opportunity->status !== OpportunityStatus::IN_VERIFICA) {
            throw new DomainException('Solo un\'opportunità in verifica può essere respinta.');
        }

        if (trim($reason) === '') {
            throw new DomainException('La motivazione del rifiuto è obbligatoria.');
        }

        DB::transaction(function () use ($opportunity, $tecnico, $reason) {
            OpportunityReview::create([
                'opportunity_id' => $opportunity->id,
                'reviewer_id' => $tecnico->id,
                'outcome' => ReviewOutcome::RESPINTA,
                'notes' => $reason,
            ]);

            $this->transition($opportunity, OpportunityStatus::DA_CORREGGERE, $tecnico, [
                'reviewed_by' => $tecnico->id,
                'review_notes' => $reason,
            ]);
        });

        $this->notifications->notifyUser(
            $opportunity->creator,
            $opportunity,
            NotificationType::OPPORTUNITA_RESPINTA,
            'Da correggere: '.$opportunity->title,
            $reason,
        );

        return $opportunity;
    }

    // ------------------------------------------------------------- automatismi

    /** PROGRAMMATA → APERTA. Idempotente: se non è più programmata non fa nulla. */
    public function open(Opportunity $opportunity): bool
    {
        if ($opportunity->status !== OpportunityStatus::PROGRAMMATA || $opportunity->opens_at->greaterThan(now())) {
            return false;
        }

        $this->transition($opportunity, OpportunityStatus::APERTA, null, ['published_at' => now()]);
        $this->announceOpening($opportunity);

        return true;
    }

    /** APERTA → SCADUTA. Idempotente. */
    public function expire(Opportunity $opportunity): bool
    {
        if ($opportunity->status !== OpportunityStatus::APERTA || $opportunity->closes_at->greaterThan(now())) {
            return false;
        }

        $this->transition($opportunity, OpportunityStatus::SCADUTA, null, []);

        $this->notifications->notifyFinalSummary($opportunity);

        return true;
    }

    // ------------------------------------------------------------- chiusura

    public function close(Opportunity $opportunity, User $user, string $reason): Opportunity
    {
        if (trim($reason) === '') {
            throw new DomainException('La motivazione della chiusura è obbligatoria.');
        }

        $this->transition($opportunity, OpportunityStatus::CHIUSA, $user, [
            'closed_by' => $user->id,
            'closed_at' => now(),
            'close_reason' => $reason,
        ]);

        $this->notifications->notifyStores(
            $opportunity,
            NotificationType::OPPORTUNITA_CHIUSA,
            'Chiusa: '.$opportunity->title,
            $reason,
        );

        $this->notifications->notifyFinalSummary($opportunity);

        return $opportunity;
    }

    public function cancel(Opportunity $opportunity, User $user, string $reason): Opportunity
    {
        if (trim($reason) === '') {
            throw new DomainException('La motivazione dell\'annullamento è obbligatoria.');
        }

        DB::transaction(function () use ($opportunity, $user, $reason) {
            $locked = $this->availability->lock($opportunity);

            // L'annullamento libera tutto lo stock impegnato.
            foreach ($opportunity->responses()->where('committed_packages', '>', 0)->get() as $response) {
                $this->availability->release($locked, $response);
                $response->save();
            }

            $this->transition($opportunity, OpportunityStatus::ANNULLATA, $user, [
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);
        });

        $this->notifications->notifyStores(
            $opportunity,
            NotificationType::OPPORTUNITA_ANNULLATA,
            'Annullata: '.$opportunity->title,
            $reason,
        );

        return $opportunity;
    }

    public function archive(Opportunity $opportunity, User $user): Opportunity
    {
        $this->transition($opportunity, OpportunityStatus::ARCHIVIATA, $user, ['archived_at' => now()]);

        return $opportunity;
    }

    // ------------------------------------------------------------- duplicazione

    /** Copia articolo, testi, prezzi e confezionamento. Mai risposte, scadenze, consegna, stock. */
    public function duplicate(Opportunity $source, User $buyer): Opportunity
    {
        $copy = $source->replicate([
            'reference', 'status', 'committed_packages', 'total_packages',
            'opens_at', 'closes_at', 'delivery_date',
            'submitted_at', 'approved_at', 'published_at', 'closed_at', 'cancelled_at', 'archived_at',
            'reviewed_by', 'closed_by', 'cancelled_by', 'review_notes', 'close_reason', 'cancel_reason',
            'media_exception', 'media_exception_reason',
        ]);

        return DB::transaction(function () use ($copy, $source, $buyer) {
            $copy->reference = Opportunity::nextReference();
            $copy->status = OpportunityStatus::BOZZA;
            $copy->created_by = $buyer->id;
            $copy->committed_packages = 0;
            $copy->total_packages = null;
            $copy->availability_type = AvailabilityType::APERTA;
            $copy->opens_at = now();
            $copy->closes_at = now()->addDay();
            $copy->delivery_date = now()->addDays(2)->toDateString();
            $copy->media_exception = false;
            $copy->save();

            $copy->stores()->sync($source->stores()->pluck('stores.id')->all());

            $this->audit->log('opportunity.duplicated', $copy, [
                'origine' => $source->reference,
            ], $buyer);

            return $copy->fresh(['stores']);
        });
    }

    // ------------------------------------------------------------- validazioni

    public function assertReadyForReview(Opportunity $opportunity): void
    {
        if (! $opportunity->status->isEditableByBuyer()) {
            throw new DomainException('Solo una bozza o un\'opportunità da correggere può essere inviata in verifica.');
        }

        $this->assertPublishable($opportunity);
    }

    /** Requisiti minimi di pubblicazione (capitolato §5). */
    public function assertPublishable(Opportunity $opportunity): void
    {
        $errors = $this->publishIssues($opportunity);

        if ($errors !== []) {
            throw new DomainException(implode(' ', $errors));
        }
    }

    /** @return string[] elenco leggibile dei problemi che impediscono la pubblicazione */
    public function publishIssues(Opportunity $opportunity): array
    {
        $issues = [];

        if ($opportunity->closes_at->lessThanOrEqualTo($opportunity->opens_at)) {
            $issues[] = 'La scadenza deve essere successiva all\'apertura.';
        }

        $closesDate = $opportunity->closes_at->copy()->setTimezone(config('app.display_timezone'))->startOfDay();
        if ($opportunity->delivery_date->copy()->startOfDay()->lessThan($closesDate)) {
            $issues[] = 'La data di consegna non può precedere la scadenza.';
        }

        if ($opportunity->isLimited() && (int) $opportunity->total_packages <= 0) {
            $issues[] = 'Con disponibilità limitata è obbligatorio indicare il totale dei colli.';
        }

        if ((float) $opportunity->kg_per_package <= 0) {
            $issues[] = 'I kg per collo devono essere maggiori di zero.';
        }

        if ($opportunity->stores()->count() === 0) {
            $issues[] = 'Seleziona almeno un punto vendita destinatario.';
        }

        if (! $opportunity->hasMedia() && ! $opportunity->media_exception) {
            $issues[] = 'Serve almeno una foto o un video, salvo eccezione autorizzata dal Tecnico.';
        }

        return $issues;
    }

    /** Eccezione tracciata: pubblicazione senza media, autorizzata dal Tecnico. */
    public function grantMediaException(Opportunity $opportunity, User $tecnico, string $reason): void
    {
        if (trim($reason) === '') {
            throw new DomainException('La motivazione dell\'eccezione è obbligatoria.');
        }

        $opportunity->forceFill([
            'media_exception' => true,
            'media_exception_reason' => $reason,
        ])->save();

        $this->audit->log('opportunity.media_exception', $opportunity, ['motivazione' => $reason], $tecnico);
    }

    // ------------------------------------------------------------- interne

    private function transition(Opportunity $opportunity, OpportunityStatus $target, ?User $user, array $extra): void
    {
        $from = $opportunity->status;

        if (! $from->canTransitionTo($target)) {
            throw new InvalidTransitionException($from, $target);
        }

        $opportunity->forceFill(array_merge($extra, ['status' => $target]))->save();

        $this->audit->log('opportunity.transition', $opportunity, [
            'da' => $from->value,
            'a' => $target->value,
        ], $user);
    }

    private function announceOpening(Opportunity $opportunity): void
    {
        // Prepara la riga di risposta per ogni destinatario: "non compilata" è uno stato esplicito.
        foreach ($opportunity->stores()->pluck('stores.id') as $storeId) {
            $opportunity->responses()->firstOrCreate(
                ['store_id' => $storeId],
                ['status' => ResponseStatus::NON_COMPILATA, 'packages' => 0, 'kg' => 0],
            );
        }

        $this->notifications->notifyStores(
            $opportunity,
            NotificationType::OPPORTUNITA_APERTA,
            'Nuova opportunità: '.$opportunity->title,
            'Scadenza '.Format::dateTime($opportunity->closes_at).' — consegna '.Format::date($opportunity->delivery_date).'.',
        );
    }
}
