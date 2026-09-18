<?php

namespace App\Livewire\Cr;

use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\Response;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pagina d'ingresso del Capo Reparto: l'elenco della merce disponibile.
 *
 * Non è una dashboard e non deve diventarlo: chi apre l'applicazione da un
 * reparto vuole vedere subito che cosa c'è da ordinare, non dei contatori.
 * I conteggi vivono quindi nelle schede di filtro, non in riquadri dedicati.
 * Tutte le query passano da Opportunity::visibleTo(), quindi il perimetro del
 * punto vendita è applicato lato server e non dipende dall'interfaccia.
 */
#[Layout('components.layouts.app')]
#[Title('Opportunità')]
class Opportunita extends Component
{
    use WithPagination;

    #[Url]
    public string $vista = 'da_completare';

    #[Url]
    public string $categoria = '';

    #[Url]
    public string $ricerca = '';

    public function aggiornaVista(string $vista): void
    {
        $this->vista = $vista;
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['categoria', 'ricerca'], true)) {
            $this->resetPage();
        }
    }

    private function baseQuery()
    {
        $utente = auth()->user();

        return Opportunity::query()
            ->visibleTo($utente)
            ->with(['media'])
            ->withCount('stores');
    }

    /** @return array<string, int> conteggi per le schede in alto */
    public function conteggi(): array
    {
        $utente = auth()->user();
        $risposte = Response::where('store_id', $utente->store_id)->pluck('status', 'opportunity_id');

        $aperte = Opportunity::visibleTo($utente)
            ->where('status', OpportunityStatus::APERTA)
            ->where('closes_at', '>', now())
            ->pluck('id');

        $daCompletare = $aperte->filter(fn ($id) => ! ($risposte[$id] ?? ResponseStatus::NON_COMPILATA)->isSubmitted())->count();
        $bozze = $aperte->filter(fn ($id) => ($risposte[$id] ?? null) === ResponseStatus::BOZZA)->count();
        $inviate = $aperte->filter(fn ($id) => ($risposte[$id] ?? ResponseStatus::NON_COMPILATA)->isSubmitted())->count();

        return [
            'da_completare' => $daCompletare,
            'bozze' => $bozze,
            'inviate' => $inviate,
        ];
    }

    public function render()
    {
        $utente = auth()->user();
        $query = $this->baseQuery();

        if ($this->categoria !== '') {
            $query->where('category', $this->categoria);
        }

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->ricerca}%")
                    ->orWhere('article_code', 'like', "%{$this->ricerca}%")
                    ->orWhere('plu', 'like', "%{$this->ricerca}%")
                    ->orWhere('title', 'like', "%{$this->ricerca}%");
            });
        }

        $risposte = Response::where('store_id', $utente->store_id)->get()->keyBy('opportunity_id');

        match ($this->vista) {
            'storico' => $query->whereIn('status', [OpportunityStatus::SCADUTA, OpportunityStatus::CHIUSA, OpportunityStatus::ANNULLATA])
                ->orderByDesc('closes_at'),
            default => $query->where('status', OpportunityStatus::APERTA)->orderBy('closes_at'),
        };

        $opportunita = $query->paginate(12);

        // Il filtro per stato della risposta si applica sulla pagina corrente.
        $filtrate = $opportunita->getCollection()->filter(function (Opportunity $o) use ($risposte) {
            $stato = $risposte[$o->id]->status ?? ResponseStatus::NON_COMPILATA;

            return match ($this->vista) {
                'da_completare' => ! $stato->isSubmitted(),
                'bozze' => $stato === ResponseStatus::BOZZA,
                'inviate' => $stato->isSubmitted(),
                default => true,
            };
        });

        $opportunita->setCollection($filtrate);

        // Quanto hanno già ordinato gli altri punti vendita: visibile a tutti
        // per creare emulazione fra i reparti.
        $ordinato = Response::whereIn('opportunity_id', $opportunita->getCollection()->pluck('id'))
            ->where('status', ResponseStatus::INVIATA_ACQUISTO)
            ->selectRaw('opportunity_id, COALESCE(SUM(packages), 0) as colli, COUNT(*) as punti_vendita')
            ->groupBy('opportunity_id')
            ->get()
            ->keyBy('opportunity_id');

        return view('livewire.cr.opportunita', [
            'opportunita' => $opportunita,
            'risposte' => $risposte,
            'ordinato' => $ordinato,
            'conteggi' => $this->conteggi(),
            'categorie' => Opportunity::visibleTo($utente)->distinct()->pluck('category')->filter()->values(),
            'prossimaScadenza' => Opportunity::visibleTo($utente)
                ->where('status', OpportunityStatus::APERTA)
                ->where('closes_at', '>', now())
                ->orderBy('closes_at')
                ->value('closes_at'),
        ]);
    }
}
