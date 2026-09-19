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
use Illuminate\Support\Facades\Storage;

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
     * Modifica di un'opportunità già pubblicata.
     *
     * Il Buyer può intervenire in qualsiasi momento, ma la modifica non va in
     * linea da sola: l'opportunità torna IN_VERIFICA e tocca al Tecnico
     * rimetterla a disposizione dei punti vendita. Le risposte già raccolte
     * restano dove sono.
     */
    public function updatePublished(Opportunity $opportunity, array $data, User $user): Opportunity
    {
        $this->assertModificaCompatibile($opportunity, $data);

        return DB::transaction(function () use ($opportunity, $data, $user) {
            $before = $opportunity->only(array_keys($data));
            $kgPerColloPrima = (float) $opportunity->kg_per_package;

            $opportunity->fill($data);
            $opportunity->recalculatePricing();
            $opportunity->save();

            if (array_key_exists('store_ids', $data)) {
                $opportunity->stores()->sync($data['store_ids']);
            }

            // Cambiando il peso del collo, i kg già registrati sulle risposte
            // sarebbero incoerenti: si riallineano subito.
            $kgPerColloDopo = (float) $opportunity->kg_per_package;

            if (abs($kgPerColloDopo - $kgPerColloPrima) > 0.0001) {
                foreach ($opportunity->responses()->where('packages', '>', 0)->get() as $risposta) {
                    $risposta->forceFill([
                        'kg' => round($risposta->packages * $kgPerColloDopo, 3),
                    ])->save();
                }

                $this->audit->log('opportunity.kg_recalculated', $opportunity, [
                    'kg_per_collo_prima' => $kgPerColloPrima,
                    'kg_per_collo_dopo' => $kgPerColloDopo,
                ], $user);
            }

            $this->audit->log('opportunity.updated_published', $opportunity, [
                'prima' => $before,
                'dopo' => $opportunity->only(array_keys($data)),
            ], $user);

            // Torna in verifica: la ripubblicazione passa sempre dal Tecnico.
            $this->transition($opportunity, OpportunityStatus::IN_VERIFICA, $user, [
                'submitted_at' => now(),
            ]);

            $this->notifications->notifyStores(
                $opportunity,
                NotificationType::OPPORTUNITA_MODIFICATA,
                'In aggiornamento: '.$opportunity->title,
                'Il Buyer ha modificato '.$opportunity->reference.'. Sarà di nuovo disponibile appena il Tecnico conferma.',
            );

            $this->notifications->notifyRole(
                Role::TECNICO,
                $opportunity,
                NotificationType::OPPORTUNITA_IN_VERIFICA,
                'Da ripubblicare: '.$opportunity->title,
                $opportunity->reference.' è stata modificata dopo la pubblicazione e attende la tua conferma.',
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

        // Già pubblicata in passato: è una ripubblicazione dopo una modifica.
        $ripubblicazione = $opportunity->published_at !== null;

        $target = match (true) {
            $opportunity->closes_at->lessThanOrEqualTo(now()) => OpportunityStatus::SCADUTA,
            $opportunity->opens_at->greaterThan(now()) => OpportunityStatus::PROGRAMMATA,
            default => OpportunityStatus::APERTA,
        };

        DB::transaction(function () use ($opportunity, $tecnico, $notes, $checklist, $target) {
            OpportunityReview::create([
                'opportunity_id' => $opportunity->id,
                'reviewer_id' => $tecnico->id,
                'outcome' => ReviewOutcome::APPROVATA,
                'notes' => $notes,
                'checklist' => $checklist,
            ]);

            $this->transition($opportunity, $target, $tecnico, array_filter([
                'reviewed_by' => $tecnico->id,
                'approved_at' => now(),
                'review_notes' => $notes,
                'published_at' => $target === OpportunityStatus::APERTA ? now() : $opportunity->published_at,
            ], fn ($v) => $v !== null));
        });

        $this->notifications->notifyUser(
            $opportunity->creator,
            $opportunity,
            NotificationType::OPPORTUNITA_APPROVATA,
            'Approvata: '.$opportunity->title,
            'Apertura '.Format::dateTime($opportunity->opens_at).', scadenza '.Format::dateTime($opportunity->closes_at).'.',
        );

        if ($target === OpportunityStatus::APERTA) {
            $this->announceOpening($opportunity, $ripubblicazione);
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

    /**
     * Correzione del prezzo di vendita da parte del Tecnico durante la verifica.
     *
     * È l'unico dato che il Tecnico modifica direttamente, e solo prima della
     * pubblicazione: dopo, il prezzo è già stato letto da chi ha ordinato.
     * Ricarico e margine si ricalcolano di conseguenza — non si digitano mai.
     */
    public function aggiornaPrezzoVendita(Opportunity $opportunity, User $tecnico, float $nuovoPrezzo, ?string $nota = null): Opportunity
    {
        if ($opportunity->status !== OpportunityStatus::IN_VERIFICA) {
            throw new DomainException('Il prezzo si corregge solo mentre l\'opportunità è in verifica.');
        }

        if ($nuovoPrezzo <= 0) {
            throw new DomainException('Il prezzo di vendita deve essere maggiore di zero.');
        }

        $prezzoPrima = (float) $opportunity->sale_price_gross;

        if (abs($nuovoPrezzo - $prezzoPrima) < 0.0001) {
            return $opportunity;        // nessuna modifica, nessuna traccia inutile
        }

        return DB::transaction(function () use ($opportunity, $tecnico, $nuovoPrezzo, $nota, $prezzoPrima) {
            $ricaricoPrima = $opportunity->markup_percent;

            $opportunity->sale_price_gross = $nuovoPrezzo;
            $opportunity->recalculatePricing();
            $opportunity->save();

            $this->audit->log('opportunity.price_updated', $opportunity, [
                'riferimento' => $opportunity->reference,
                'prezzo_prima' => round($prezzoPrima, 4),
                'prezzo_dopo' => round($nuovoPrezzo, 4),
                'ricarico_prima' => $ricaricoPrima,
                'ricarico_dopo' => $opportunity->markup_percent,
                'nota' => $nota,
            ], $tecnico);

            // Il Buyer deve saperlo: è un suo dato che è cambiato.
            $this->notifications->notifyUser(
                $opportunity->creator,
                $opportunity,
                NotificationType::OPPORTUNITA_MODIFICATA,
                'Prezzo corretto dal Tecnico: '.$opportunity->title,
                'Vendita da '.Format::money($prezzoPrima).' a '
                    .Format::money($nuovoPrezzo).'/kg'
                    .($nota ? ' — '.$nota : '').'.',
            );

            return $opportunity;
        });
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

    /**
     * Eliminazione definitiva.
     *
     * Non è l'annullamento: qui l'opportunità sparisce davvero, con media,
     * risposte e revisioni. Serve per ciò che è stato creato per errore o è
     * talmente vecchio da non meritare l'archivio.
     *
     * La motivazione è obbligatoria finché l'opportunità è recente; è invece
     * superflua se il termine è passato da oltre un mese.
     *
     * Resta la voce nell'audit log, che non ha vincoli verso le opportunità e
     * sopravvive alla cancellazione: di ciò che è stato eliminato resta traccia.
     */
    public function eliminaDefinitivamente(Opportunity $opportunity, User $user, ?string $motivo = null): void
    {
        $motivo = trim((string) $motivo);

        if ($motivo === '' && ! $opportunity->eliminabileSenzaMotivazione()) {
            throw new DomainException(
                'Serve una motivazione: l\'opportunità non è scaduta da almeno un mese.'
            );
        }

        $riepilogo = [
            'riferimento' => $opportunity->reference,
            'articolo' => $opportunity->article_code,
            'descrizione' => $opportunity->description,
            'stato' => $opportunity->status->value,
            'scadenza' => $opportunity->closes_at->toIso8601String(),
            'consegna' => $opportunity->delivery_date->toDateString(),
            'punti_vendita' => $opportunity->stores()->count(),
            'risposte_inviate' => $opportunity->responses()->submitted()->count(),
            'colli_ordinati' => $opportunity->totalPackagesOrdered(),
            'motivazione' => $motivo !== '' ? $motivo : 'non richiesta (scaduta da oltre un mese)',
        ];

        $media = $opportunity->media()->get();

        DB::transaction(function () use ($opportunity, $user, $riepilogo, $media) {
            // L'audit va scritto prima: dopo, l'opportunità non esiste più.
            $this->audit->log('opportunity.deleted', $opportunity, $riepilogo, $user);

            $opportunity->delete();

            // I file si tolgono dopo il commit logico: se la transazione
            // fallisse, meglio file orfani che righe senza contenuto.
            foreach ($media as $file) {
                Storage::disk($file->disk)->delete($file->path);

                if ($file->poster_path) {
                    Storage::disk($file->disk)->delete($file->poster_path);
                }
            }
        });
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

    /**
     * Verifica che una modifica su un'opportunità già pubblicata non renda
     * incoerenti gli ordini già raccolti.
     */
    public function assertModificaCompatibile(Opportunity $opportunity, array $data): void
    {
        $impegnati = (int) $opportunity->committed_packages;

        if (array_key_exists('total_packages', $data) && $data['total_packages'] !== null) {
            $nuovoTotale = (int) $data['total_packages'];

            if ($nuovoTotale < $impegnati) {
                throw new DomainException(
                    "Non puoi scendere a {$nuovoTotale} colli: ne sono già stati confermati {$impegnati}."
                );
            }
        }

        // Passare da disponibilità limitata ad aperta è sempre consentito;
        // il contrario, con ordini già raccolti, richiede un totale capiente.
        if (array_key_exists('availability_type', $data)) {
            $tipo = $data['availability_type'];
            $diventaLimitata = ($tipo instanceof AvailabilityType ? $tipo : AvailabilityType::from((string) $tipo))
                === AvailabilityType::LIMITATA;

            if ($diventaLimitata && ($data['total_packages'] ?? null) === null && $impegnati > 0) {
                throw new DomainException('Indica il totale dei colli: ce ne sono già '.$impegnati.' confermati.');
            }
        }

        if (array_key_exists('store_ids', $data)) {
            $rimossi = $opportunity->stores()
                ->whereNotIn('stores.id', $data['store_ids'] ?: [0])
                ->pluck('stores.id');

            $conRisposta = $opportunity->responses()
                ->whereIn('store_id', $rimossi)
                ->submitted()
                ->with('store')
                ->get();

            if ($conRisposta->isNotEmpty()) {
                $codici = $conRisposta->map(fn ($r) => $r->store->code)->implode(', ');

                throw new DomainException(
                    "Non puoi togliere punti vendita che hanno già risposto: {$codici}."
                );
            }
        }
    }

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

    private function announceOpening(Opportunity $opportunity, bool $ripubblicazione = false): void
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
