<?php

namespace App\Livewire\Shared;

use App\Enums\ResponseStatus;
use App\Enums\Role;
use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Models\Response;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OpportunityWorkflowService;
use App\Services\ResponseSubmissionService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Scheda completa dell'opportunità per Buyer e Tecnico, con le risposte dei punti vendita. */
#[Layout('components.layouts.app')]
class OpportunitaShow extends Component
{
    public Opportunity $opportunity;

    public string $azione = '';          // 'chiudi' | 'annulla' | 'riapri'

    public string $motivazione = '';

    public ?int $rispostaDaRiaprire = null;

    public string $nuovaScadenza = '';

    public function mount(Opportunity $opportunity): void
    {
        $this->authorize('view', $opportunity);

        $this->opportunity = $opportunity->load(['media', 'stores', 'creator', 'reviewer', 'reviews.reviewer']);
        $this->nuovaScadenza = now(config('app.display_timezone'))->addHours(4)->format('Y-m-d\TH:i');
    }

    public function apriAzione(string $azione, ?int $rispostaId = null): void
    {
        $this->azione = $azione;
        $this->motivazione = '';
        $this->rispostaDaRiaprire = $rispostaId;
        $this->resetErrorBag();
    }

    public function conferma(): void
    {
        try {
            match ($this->azione) {
                'chiudi' => $this->chiudi(),
                'annulla' => $this->annulla(),
                'riapri' => $this->riapri(),
                default => null,
            };

            $this->azione = '';
            $this->opportunity->refresh();
        } catch (DomainException $e) {
            $this->addError('azione', $e->getMessage());
        }
    }

    private function chiudi(): void
    {
        $this->authorize('close', $this->opportunity);
        app(OpportunityWorkflowService::class)->close($this->opportunity, auth()->user(), $this->motivazione);
        $this->dispatch('toast', messaggio: 'Opportunità chiusa.');
    }

    private function annulla(): void
    {
        $this->authorize('cancel', $this->opportunity);
        app(OpportunityWorkflowService::class)->cancel($this->opportunity, auth()->user(), $this->motivazione);
        $this->dispatch('toast', messaggio: 'Opportunità annullata.');
    }

    private function riapri(): void
    {
        $risposta = Response::findOrFail($this->rispostaDaRiaprire);
        $this->authorize('reopen', $risposta);

        app(ResponseSubmissionService::class)->reopen(
            $risposta,
            auth()->user(),
            Carbon::parse($this->nuovaScadenza, config('app.display_timezone')),
            $this->motivazione,
        );

        $this->dispatch('toast', messaggio: 'Risposta riaperta.');
    }

    public function duplica(): void
    {
        $this->authorize('duplicate', $this->opportunity);

        $copia = app(OpportunityWorkflowService::class)->duplicate($this->opportunity, auth()->user());

        session()->flash('status', 'Creata la bozza '.$copia->reference.' a partire da '.$this->opportunity->reference.'.');

        $this->redirectRoute('buyer.opportunita.edit', $copia, navigate: true);
    }

    public function sollecita(): void
    {
        abort_unless(auth()->user()->isTecnico() || auth()->user()->isBuyer(), 403);

        $inviati = app(NotificationService::class)->remindMissing($this->opportunity, 'manuale-'.now()->format('YmdHi'));

        $this->dispatch('toast', messaggio: $inviati > 0
            ? "Sollecito inviato a {$inviati} capi reparto."
            : 'Nessun punto vendita da sollecitare.');
    }

    public function render()
    {
        $risposte = $this->opportunity->responses()->with(['store', 'lastActor'])->get()->keyBy('store_id');

        return view('livewire.shared.opportunita-show', [
            'risposte' => $risposte,
            'tecnici' => User::where('role', Role::TECNICO)
                ->where('is_active', true)
                ->orderBy('last_name')
                ->get(),
            'mancanti' => $this->opportunity->stores
                ->reject(fn ($store) => ($risposte[$store->id] ?? null)?->isSubmitted())
                ->map(fn ($store) => [
                    'store' => $store,
                    'utenti' => User::where('role', Role::CAPO_REPARTO)
                        ->where('store_id', $store->id)
                        ->where('is_active', true)
                        ->get(),
                ])
                ->values(),
            'statistiche' => $this->opportunity->completionStats(),
            'colliTotali' => $this->opportunity->totalPackagesOrdered(),
            'kgTotali' => $this->opportunity->totalKgOrdered(),
            'statoPredefinito' => ResponseStatus::NON_COMPILATA,
        ])->title($this->opportunity->reference.' — '.$this->opportunity->title);
    }
}
