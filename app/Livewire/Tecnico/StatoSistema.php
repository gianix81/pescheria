<?php

namespace App\Livewire\Tecnico;

use App\Enums\Role;
use App\Models\Opportunity;
use App\Models\Store;
use App\Models\User;
use App\Support\Sistema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Stato dell'ambiente, leggibile senza aprire una shell.
 *
 * Risponde alle domande che durante la messa in linea tornano di continuo:
 * è pubblicata l'ultima versione? il database è allineato? i file sopravvivono
 * alle pubblicazioni? lo scheduler sta girando?
 */
#[Layout('components.layouts.app')]
#[Title('Stato sistema')]
class StatoSistema extends Component
{
    public function mount(): void
    {
        $this->authorize('gestire-account');
    }

    public function render()
    {
        $scheduler = Sistema::ultimaEsecuzioneScheduler();

        return view('livewire.tecnico.stato-sistema', [
            'versione' => Sistema::versione(),
            'ambiente' => config('app.env'),
            'demo' => (bool) config('pescheria.demo.enabled'),
            'databaseOk' => Sistema::databaseRaggiungibile(),
            'migrazioni' => Sistema::migrazioniNonApplicate(),
            'media' => Sistema::media(),
            'scheduler' => $scheduler,
            'schedulerMinuti' => $scheduler ? (int) $scheduler->diffInMinutes(now()) : null,
            'utenti' => collect(Role::cases())->mapWithKeys(fn (Role $r) => [
                $r->label() => User::where('role', $r)->where('is_active', true)->count(),
            ]),
            'puntiVendita' => Store::count(),
            'opportunita' => Opportunity::count(),
        ]);
    }
}
