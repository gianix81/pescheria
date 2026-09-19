<?php

namespace App\Livewire\Tecnico;

use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Services\OpportunityWorkflowService;
use App\Support\Format;
use App\Support\PricingCalculator;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Schermata di verifica: anteprima completa a sinistra, checklist e decisione a destra. */
#[Layout('components.layouts.app')]
class Verifica extends Component
{
    public Opportunity $opportunity;

    public string $note = '';

    public string $motivazioneRifiuto = '';

    public bool $rifiutoAperto = false;

    public string $motivazioneEccezioneMedia = '';

    public bool $eccezioneMediaAperta = false;

    /** @var array<string, bool> */
    public array $checklist = [];

    /** Prezzo di vendita in correzione, in ora italiana di lavoro del Tecnico. */
    public $prezzoVendita = null;

    public string $notaPrezzo = '';

    public function mount(Opportunity $opportunity): void
    {
        $this->authorize('review', $opportunity);

        $this->opportunity = $opportunity->load(['media', 'stores', 'creator']);
        $this->prezzoVendita = (float) $opportunity->sale_price_gross;
    }

    /**
     * Ricarico e margine che risulterebbero dal prezzo digitato, prima di
     * salvare: il Tecnico vede l'effetto della correzione mentre la fa.
     *
     * @return array{net: float, markup: ?float, margin: ?float}
     */
    public function getPrezziPropostiProperty(): array
    {
        return PricingCalculator::all(
            (float) $this->opportunity->purchase_price,
            (float) ($this->prezzoVendita ?: 0),
            (float) $this->opportunity->vat_rate,
        );
    }

    public function aggiornaPrezzo(): void
    {
        $this->authorize('updatePrice', $this->opportunity);

        $this->validate([
            'prezzoVendita' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'notaPrezzo' => ['nullable', 'string', 'max:500'],
        ], [], ['prezzoVendita' => 'prezzo di vendita']);

        try {
            $this->opportunity = app(OpportunityWorkflowService::class)->aggiornaPrezzoVendita(
                $this->opportunity,
                auth()->user(),
                (float) $this->prezzoVendita,
                $this->notaPrezzo ?: null,
            )->load(['media', 'stores', 'creator']);

            $this->notaPrezzo = '';
            $this->dispatch('toast', messaggio: 'Prezzo aggiornato: il Buyer è stato avvisato.');
        } catch (DomainException $e) {
            $this->addError('verifica', $e->getMessage());
        }
    }

    public function approva(): void
    {
        $this->authorize('review', $this->opportunity);

        try {
            app(OpportunityWorkflowService::class)->approve(
                $this->opportunity, auth()->user(), $this->note ?: null, $this->checklist,
            );

            session()->flash('status', 'Opportunità approvata. Apertura '
                .Format::dateTime($this->opportunity->opens_at)
                .', scadenza '.Format::dateTime($this->opportunity->closes_at).'.');

            $this->redirectRoute('opportunita.show', $this->opportunity, navigate: true);
        } catch (DomainException $e) {
            $this->addError('verifica', $e->getMessage());
        }
    }

    public function respingi(): void
    {
        $this->authorize('review', $this->opportunity);

        try {
            app(OpportunityWorkflowService::class)->reject($this->opportunity, auth()->user(), $this->motivazioneRifiuto);

            session()->flash('status', 'Opportunità rimandata al Buyer con la motivazione indicata.');

            $this->redirectRoute('tecnico.dashboard', navigate: true);
        } catch (DomainException $e) {
            $this->addError('verifica', $e->getMessage());
        }
    }

    /** Eccezione tracciata: pubblicazione senza foto né video. */
    public function concediEccezioneMedia(): void
    {
        try {
            app(OpportunityWorkflowService::class)->grantMediaException(
                $this->opportunity, auth()->user(), $this->motivazioneEccezioneMedia,
            );

            $this->eccezioneMediaAperta = false;
            $this->opportunity->refresh();
            $this->dispatch('toast', messaggio: 'Eccezione registrata nell\'audit log.');
        } catch (DomainException $e) {
            $this->addError('verifica', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tecnico.verifica', [
            'problemi' => app(OpportunityWorkflowService::class)->publishIssues($this->opportunity),
        ])->title('Verifica '.$this->opportunity->reference);
    }
}
