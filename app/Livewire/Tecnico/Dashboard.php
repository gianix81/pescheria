<?php

namespace App\Livewire\Tecnico;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Services\NotificationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard Tecnico')]
class Dashboard extends Component
{
    public function sollecita(int $opportunityId): void
    {
        $opportunita = Opportunity::findOrFail($opportunityId);

        $inviati = app(NotificationService::class)->remindMissing($opportunita, 'manuale-'.now()->format('YmdHi'));

        $this->dispatch('toast', messaggio: $inviati > 0
            ? "Sollecito inviato a {$inviati} punti vendita."
            : 'Tutti i punti vendita hanno già risposto.');
    }

    public function render()
    {
        $daVerificare = Opportunity::where('status', OpportunityStatus::IN_VERIFICA)
            ->with('creator')->orderBy('submitted_at')->get();

        $inScadenzaOggi = Opportunity::where('status', OpportunityStatus::APERTA)
            ->whereBetween('closes_at', [now(), now()->endOfDay()])
            ->orderBy('closes_at')->get();

        $monitor = Opportunity::whereIn('status', [OpportunityStatus::APERTA, OpportunityStatus::SCADUTA])
            ->with(['stores', 'responses'])
            ->orderBy('closes_at')
            ->limit(15)
            ->get()
            ->map(fn (Opportunity $o) => ['opportunita' => $o, 'stat' => $o->completionStats()]);

        $anomalie = $monitor
            ->filter(fn ($riga) => $riga['stat']['bozze'] > 0
                || ($riga['opportunita']->status === OpportunityStatus::SCADUTA && $riga['stat']['mancanti'] > 0))
            ->values();

        return view('livewire.tecnico.dashboard', [
            'daVerificare' => $daVerificare,
            'inScadenzaOggi' => $inScadenzaOggi,
            'monitor' => $monitor,
            'anomalie' => $anomalie,
            'mancantiTotali' => $monitor->sum(fn ($r) => $r['stat']['mancanti']),
        ]);
    }
}
