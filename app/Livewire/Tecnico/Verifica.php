<?php

namespace App\Livewire\Tecnico;

use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Services\OpportunityWorkflowService;
use App\Support\Format;
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

    public function mount(Opportunity $opportunity): void
    {
        $this->authorize('review', $opportunity);

        $this->opportunity = $opportunity->load(['media', 'stores', 'creator']);
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
