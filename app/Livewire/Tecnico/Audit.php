<?php

namespace App\Livewire\Tecnico;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Audit log')]
class Audit extends Component
{
    use WithPagination;

    #[Url]
    public string $azione = '';

    #[Url]
    public string $ricerca = '';

    public function render()
    {
        $query = AuditLog::with('user')->latest('id');

        if ($this->azione !== '') {
            $query->where('action', $this->azione);
        }

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('payload', 'like', "%{$this->ricerca}%")
                    ->orWhere('action', 'like', "%{$this->ricerca}%");
            });
        }

        return view('livewire.tecnico.audit', [
            'voci' => $query->paginate(30),
            'azioni' => AuditLog::select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
