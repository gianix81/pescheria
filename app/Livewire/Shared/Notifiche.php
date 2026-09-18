<?php

namespace App\Livewire\Shared;

use App\Models\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Notifiche')]
class Notifiche extends Component
{
    use WithPagination;

    public bool $soloNonLette = false;

    public function segnaLetta(int $id): void
    {
        Notification::where('user_id', auth()->id())->whereKey($id)->update(['read_at' => now()]);
    }

    public function segnaTutteLette(): void
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        $query = Notification::where('user_id', auth()->id())->latest();

        if ($this->soloNonLette) {
            $query->whereNull('read_at');
        }

        return view('livewire.shared.notifiche', [
            'notifiche' => $query->paginate(20),
        ]);
    }
}
