<?php

namespace App\Livewire\Shared;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Elenco opportunità per Buyer e Tecnico, con preset (tutte, storico, da verificare). */
#[Layout('components.layouts.app')]
#[Title('Opportunità')]
class OpportunitaIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $preset = 'tutte';

    #[Url]
    public string $ricerca = '';

    #[Url]
    public string $stato = '';

    #[Url]
    public string $consegna = '';

    public function mount(?string $preset = null): void
    {
        $this->preset = $preset ?? request('preset', $this->preset);
    }

    public function updated($property): void
    {
        if (in_array($property, ['ricerca', 'stato', 'consegna', 'preset'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Opportunity::query()
            ->visibleTo(auth()->user())
            ->with(['creator'])
            ->withCount('stores');

        match ($this->preset) {
            'da_verificare' => $query->where('status', OpportunityStatus::IN_VERIFICA)->orderBy('submitted_at'),
            'storico' => $query->whereIn('status', [
                OpportunityStatus::SCADUTA, OpportunityStatus::CHIUSA,
                OpportunityStatus::ANNULLATA, OpportunityStatus::ARCHIVIATA,
            ])->orderByDesc('closes_at'),
            default => $query->orderByDesc('created_at'),
        };

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('reference', 'like', "%{$this->ricerca}%")
                    ->orWhere('article_code', 'like', "%{$this->ricerca}%")
                    ->orWhere('plu', 'like', "%{$this->ricerca}%")
                    ->orWhere('description', 'like', "%{$this->ricerca}%")
                    ->orWhere('title', 'like', "%{$this->ricerca}%");
            });
        }

        if ($this->stato !== '') {
            $query->where('status', $this->stato);
        }

        if ($this->consegna !== '') {
            $query->whereDate('delivery_date', $this->consegna);
        }

        return view('livewire.shared.opportunita-index', [
            'opportunita' => $query->paginate(20),
            'stati' => OpportunityStatus::cases(),
        ]);
    }
}
