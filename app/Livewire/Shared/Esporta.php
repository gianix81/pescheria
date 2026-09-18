<?php

namespace App\Livewire\Shared;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\Store;
use App\Services\ExportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Pagina di export: l'anteprima mostra esattamente ciò che il file conterrà. */
#[Layout('components.layouts.app')]
#[Title('Export')]
class Esporta extends Component
{
    public ?int $opportunity_id = null;

    public array $status = [];

    public string $delivery_from = '';

    public string $delivery_to = '';

    public ?int $store_id = null;

    public function mount(): void
    {
        $this->authorize('export', Opportunity::class);
    }

    public function filtri(): array
    {
        return array_filter([
            'opportunity_id' => $this->opportunity_id,
            'status' => $this->status,
            'delivery_from' => $this->delivery_from,
            'delivery_to' => $this->delivery_to,
            'store_id' => $this->store_id,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    public function render()
    {
        $anteprima = app(ExportService::class)->opportunities($this->filtri());

        return view('livewire.shared.esporta', [
            'anteprima' => $anteprima->take(25),
            'totale' => $anteprima->count(),
            'stati' => OpportunityStatus::cases(),
            'puntiVendita' => Store::orderBy('code')->get(),
            'opportunitaElenco' => Opportunity::orderByDesc('closes_at')->limit(100)->get(),
        ]);
    }
}
