<?php

namespace App\Livewire\Tecnico\Anagrafiche;

use App\Models\Store;
use App\Services\AuditService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Punti vendita')]
class PuntiVendita extends Component
{
    use WithPagination;

    public ?int $modificaId = null;

    public string $ricerca = '';

    public array $form = [
        'code' => '', 'portal_code' => '', 'name' => '', 'address' => '', 'city' => '',
        'province' => '', 'email' => '', 'phone' => '', 'is_active' => true,
    ];

    public function mount(): void
    {
        $this->authorize('gestire-anagrafiche');
    }

    public function modifica(int $id): void
    {
        $store = Store::findOrFail($id);
        $this->modificaId = $id;
        $this->form = $store->only(array_keys($this->form));
    }

    public function nuovo(): void
    {
        $this->modificaId = null;
        $this->form = ['code' => '', 'portal_code' => '', 'name' => '', 'address' => '', 'city' => '', 'province' => '', 'email' => '', 'phone' => '', 'is_active' => true];
    }

    public function salva(): void
    {
        $this->authorize('gestire-anagrafiche');

        $dati = $this->validate([
            'form.code' => ['required', 'string', 'max:20', 'unique:stores,code'.($this->modificaId ? ','.$this->modificaId : '')],
            'form.portal_code' => ['nullable', 'string', 'max:20'],
            'form.name' => ['required', 'string', 'max:160'],
            'form.address' => ['nullable', 'string', 'max:190'],
            'form.city' => ['nullable', 'string', 'max:120'],
            'form.province' => ['nullable', 'string', 'size:2'],
            'form.email' => ['nullable', 'email', 'max:190'],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.is_active' => ['boolean'],
        ])['form'];

        $store = $this->modificaId ? Store::findOrFail($this->modificaId) : new Store;
        $store->fill($dati)->save();

        app(AuditService::class)->log(
            $this->modificaId ? 'store.updated' : 'store.created', $store, ['codice' => $store->code],
        );

        $this->nuovo();
        $this->dispatch('toast', messaggio: 'Punto vendita salvato.');
    }

    public function render()
    {
        $query = Store::orderBy('code');

        if ($this->ricerca !== '') {
            $query->where(fn ($q) => $q->where('code', 'like', "%{$this->ricerca}%")->orWhere('name', 'like', "%{$this->ricerca}%"));
        }

        return view('livewire.tecnico.anagrafiche.punti-vendita', ['elenco' => $query->paginate(15)]);
    }
}
