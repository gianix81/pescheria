<?php

namespace App\Livewire\Buyer;

use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Models\Response;
use App\Services\ResponseSubmissionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Elenco risposte per il Buyer, con correzione eccezionale post-scadenza tracciata. */
#[Layout('components.layouts.app')]
#[Title('Ordini e risposte')]
class Ordini extends Component
{
    use WithPagination;

    #[Url]
    public string $stato = '';

    #[Url]
    public ?int $opportunita = null;

    public ?int $correzioneId = null;

    public int $correzioneColli = 0;

    public string $correzioneMotivo = '';

    public function apriCorrezione(int $responseId): void
    {
        $risposta = Response::findOrFail($responseId);
        $this->authorize('override', $risposta);

        $this->correzioneId = $responseId;
        $this->correzioneColli = (int) $risposta->packages;
        $this->correzioneMotivo = '';
    }

    public function salvaCorrezione(): void
    {
        $risposta = Response::findOrFail($this->correzioneId);
        $this->authorize('override', $risposta);

        try {
            app(ResponseSubmissionService::class)->overrideAfterDeadline(
                $risposta, auth()->user(), $this->correzioneColli, $this->correzioneMotivo,
            );

            $this->correzioneId = null;
            $this->dispatch('toast', messaggio: 'Correzione registrata nell\'audit log.');
        } catch (DomainException $e) {
            $this->addError('correzione', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Response::query()
            ->with(['opportunity', 'store', 'lastActor'])
            ->whereHas('opportunity')
            ->latest('updated_at');

        if ($this->stato !== '') {
            $query->where('status', $this->stato);
        }

        if ($this->opportunita) {
            $query->where('opportunity_id', $this->opportunita);
        }

        return view('livewire.buyer.ordini', [
            'risposte' => $query->paginate(25),
            'stati' => ResponseStatus::cases(),
            'opportunitaElenco' => Opportunity::whereNotIn('status', [OpportunityStatus::BOZZA])
                ->orderByDesc('closes_at')->limit(50)->get(),
        ]);
    }
}
