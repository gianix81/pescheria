<?php

namespace App\Livewire\Buyer;

use App\Enums\AvailabilityType;
use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\Response;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard Buyer')]
class Dashboard extends Component
{
    public function render()
    {
        $conteggi = Opportunity::query()
            ->selectRaw('status, count(*) as totale')
            ->groupBy('status')
            ->pluck('totale', 'status');

        // Solo quelle su cui si può ancora agire: vedi Opportunity::scopeAncoraAperta.
        $aperte = Opportunity::ancoraAperta()->pluck('id');

        // Aperte a database ma con il termine passato: lo scheduler non le ha
        // ancora spazzate. Vanno mostrate a parte, non contate fra le aperte.
        $daChiudere = Opportunity::where('status', OpportunityStatus::APERTA)
            ->where('closes_at', '<=', now())
            ->count();

        $totali = Response::whereIn('opportunity_id', $aperte)
            ->where('status', ResponseStatus::INVIATA_ACQUISTO)
            ->selectRaw('COALESCE(SUM(packages),0) as colli, COALESCE(SUM(kg),0) as kg')
            ->first();

        $destinatari = DB::table('opportunity_stores')->whereIn('opportunity_id', $aperte)->count();
        $risposte = Response::whereIn('opportunity_id', $aperte)
            ->whereIn('status', [ResponseStatus::INVIATA_ACQUISTO, ResponseStatus::INVIATA_RIFIUTO])
            ->count();

        return view('livewire.buyer.dashboard', [
            'conteggi' => $conteggi,
            'colliTotali' => (int) ($totali->colli ?? 0),
            'kgTotali' => (float) ($totali->kg ?? 0),
            'tassoRisposta' => $destinatari > 0 ? round($risposte / $destinatari * 100, 1) : 0.0,
            'inScadenza' => Opportunity::where('status', OpportunityStatus::APERTA)
                ->whereBetween('closes_at', [now(), now()->addHours(6)])
                ->orderBy('closes_at')->get(),
            'limitate' => Opportunity::ancoraAperta()
                ->where('availability_type', AvailabilityType::LIMITATA)
                ->orderBy('closes_at')->get(),
            'aperteReali' => $aperte->count(),
            'daChiudere' => $daChiudere,
            'recenti' => Opportunity::with('creator')->latest()->limit(8)->get(),
        ]);
    }
}
