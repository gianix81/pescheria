<?php

namespace App\Livewire\Cr;

use App\Enums\ResponseStatus;
use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Models\Response;
use App\Services\ResponseSubmissionService;
use App\Support\Format;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Scheda opportunità del Capo Reparto: è il flusso prioritario, pensato prima per mobile.
 * Ogni azione passa dal ResponseSubmissionService, che riverifica scadenza e quantità.
 */
#[Layout('components.layouts.app')]
class Scheda extends Component
{
    public Opportunity $opportunity;

    public ?string $decisione = null;      // 'acquista' | 'non_acquista'

    public ?int $colli = null;

    public string $motivazione = '';

    public bool $confermaAperta = false;

    public ?string $ricevuta = null;

    public function mount(Opportunity $opportunity): void
    {
        $this->authorize('view', $opportunity);
        $this->authorize('respond', [Response::class, $opportunity]);

        $this->opportunity = $opportunity->load(['media', 'stores']);

        $risposta = $this->risposta();

        if ($risposta) {
            $this->decisione = match ($risposta->status) {
                ResponseStatus::INVIATA_RIFIUTO => 'non_acquista',
                ResponseStatus::NON_COMPILATA => null,
                default => $risposta->packages > 0 ? 'acquista' : 'non_acquista',
            };
            $this->colli = $risposta->packages > 0 ? $risposta->packages : null;
            $this->motivazione = (string) $risposta->refusal_reason;
        }
    }

    public function risposta(): ?Response
    {
        return Response::where('opportunity_id', $this->opportunity->id)
            ->where('store_id', auth()->user()->store_id)
            ->first();
    }

    public function scegliColli(int $valore): void
    {
        if ($valore === 0) {
            $this->decisione = 'non_acquista';
            $this->colli = null;

            return;
        }

        $this->decisione = 'acquista';
        $this->colli = $valore;
    }

    public function incrementa(): void
    {
        $this->decisione = 'acquista';
        $this->colli = max(1, (int) $this->colli) + max(1, (int) $this->opportunity->order_multiple);
    }

    public function decrementa(): void
    {
        $nuovo = (int) $this->colli - max(1, (int) $this->opportunity->order_multiple);
        $this->colli = max((int) $this->opportunity->min_lot, $nuovo);
    }

    public function salvaBozza(): void
    {
        $this->esegui(function (ResponseSubmissionService $service) {
            $service->saveDraft(
                $this->opportunity,
                auth()->user()->store,
                auth()->user(),
                $this->decisione === 'acquista' ? (int) $this->colli : 0,
                $this->decisione === 'non_acquista' ? ($this->motivazione ?: null) : null,
            );

            $this->dispatch('toast', messaggio: 'Bozza salvata. Ricorda di inviare la risposta entro la scadenza.');
        });
    }

    public function apriConferma(): void
    {
        if ($this->decisione === null) {
            $this->addError('decisione', 'Scegli se acquistare oppure no.');

            return;
        }

        if ($this->decisione === 'acquista' && (int) $this->colli <= 0) {
            $this->addError('colli', 'Indica quanti colli vuoi ordinare.');

            return;
        }

        $this->resetErrorBag();
        $this->confermaAperta = true;
    }

    public function invia(): void
    {
        $this->esegui(function (ResponseSubmissionService $service) {
            $utente = auth()->user();

            $risposta = $this->decisione === 'acquista'
                ? $service->submitPurchase($this->opportunity, $utente->store, $utente, (int) $this->colli)
                : $service->submitRefusal($this->opportunity, $utente->store, $utente, $this->motivazione ?: null);

            $this->confermaAperta = false;
            $this->opportunity->refresh();

            $this->ricevuta = $risposta->status === ResponseStatus::INVIATA_ACQUISTO
                ? sprintf(
                    'Ordine registrato il %s: %d colli × %s kg = %s kg.',
                    Format::dateTime($risposta->submitted_at),
                    $risposta->packages,
                    Format::decimal($this->opportunity->kg_per_package, 2),
                    Format::decimal($risposta->kg, 2),
                )
                : 'Rifiuto registrato il '.Format::dateTime($risposta->submitted_at).'.';

            $this->dispatch('toast', messaggio: 'Risposta inviata.');
        });
    }

    /** Converte le eccezioni di dominio in messaggi utente, senza dettagli tecnici. */
    private function esegui(callable $azione): void
    {
        try {
            $azione(app(ResponseSubmissionService::class));
        } catch (DomainException $e) {
            $this->confermaAperta = false;
            $this->opportunity->refresh();
            $this->addError('invio', $e->getMessage());
        }
    }

    public function render()
    {
        $risposta = $this->risposta();

        return view('livewire.cr.scheda', [
            'risposta' => $risposta,
            'kgPrevisti' => round(((int) $this->colli) * (float) $this->opportunity->kg_per_package, 3),
            'apribile' => $this->opportunity->isAcceptingResponses() || ($risposta?->hasActiveReopening() ?? false),
            'residui' => $this->opportunity->remainingPackages(),
        ])->title($this->opportunity->title);
    }
}
