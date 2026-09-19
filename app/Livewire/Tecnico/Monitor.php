<?php

namespace App\Livewire\Tecnico;

use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\Response;
use App\Models\Store;
use App\Services\NotificationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Matrice opportunità × punti vendita.
 * Su schermi piccoli la vista diventa una lista raggruppata per opportunità.
 */
#[Layout('components.layouts.app')]
#[Title('Monitor compilazioni')]
class Monitor extends Component
{
    #[Url]
    public string $stato = 'APERTA';

    #[Url]
    public string $ricerca = '';

    #[Url]
    public string $consegna = '';

    /** @var array<int, array<int>> selezione mancanti: opportunity_id => store_ids */
    public array $selezione = [];

    public function toggleSelezione(int $opportunityId, int $storeId): void
    {
        $correnti = $this->selezione[$opportunityId] ?? [];

        $this->selezione[$opportunityId] = in_array($storeId, $correnti, true)
            ? array_values(array_diff($correnti, [$storeId]))
            : [...$correnti, $storeId];
    }

    public function selezionaTuttiMancanti(int $opportunityId): void
    {
        $opportunita = Opportunity::findOrFail($opportunityId);

        $this->selezione[$opportunityId] = app(NotificationService::class)->missingStoreIds($opportunita);
    }

    public function sollecitaSelezionati(int $opportunityId): void
    {
        $opportunita = Opportunity::findOrFail($opportunityId);
        $storeIds = $this->selezione[$opportunityId] ?? [];

        $inviati = app(NotificationService::class)->remindMissing(
            $opportunita,
            'manuale-'.now()->format('YmdHi'),
            $storeIds !== [] ? $storeIds : null,
        );

        $this->selezione[$opportunityId] = [];

        $this->dispatch('toast', messaggio: $inviati > 0
            ? "Sollecito inviato a {$inviati} punti vendita."
            : 'Nessun destinatario da sollecitare.');
    }

    public function render()
    {
        $query = Opportunity::query()->with('stores')->orderBy('closes_at');

        if ($this->stato !== '') {
            $query->where('status', $this->stato);
        }

        if ($this->consegna !== '') {
            $query->whereDate('delivery_date', $this->consegna);
        }

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('reference', 'like', "%{$this->ricerca}%")
                    ->orWhere('article_code', 'like', "%{$this->ricerca}%")
                    ->orWhere('description', 'like', "%{$this->ricerca}%");
            });
        }

        $opportunita = $query->limit(30)->get();

        $risposte = Response::whereIn('opportunity_id', $opportunita->pluck('id'))
            ->get()
            ->groupBy('opportunity_id')
            ->map(fn ($gruppo) => $gruppo->keyBy('store_id'));

        $puntiVendita = Store::active()
            ->whereIn('id', $opportunita->flatMap->stores->pluck('id')->unique())
            ->orderBy('code')
            ->get();

        return view('livewire.tecnico.monitor', [
            'opportunita' => $opportunita,
            'risposte' => $risposte,
            'puntiVendita' => $puntiVendita,
            'stati' => OpportunityStatus::cases(),
            'statoPredefinito' => ResponseStatus::NON_COMPILATA,
        ]);
    }
}
