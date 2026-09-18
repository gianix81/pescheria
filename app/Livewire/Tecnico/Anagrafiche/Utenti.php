<?php

namespace App\Livewire\Tecnico\Anagrafiche;

use App\Enums\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Utenti')]
class Utenti extends Component
{
    use WithPagination;

    public ?int $modificaId = null;

    public string $ricerca = '';

    public array $form = [
        'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
        'role' => 'CAPO_REPARTO', 'store_id' => null, 'is_active' => true,
    ];

    public string $password = '';

    public function mount(): void
    {
        $this->authorize('gestire-anagrafiche');
    }

    public function modifica(int $id): void
    {
        $utente = User::findOrFail($id);
        $this->modificaId = $id;
        $this->form = [
            'first_name' => $utente->first_name,
            'last_name' => $utente->last_name,
            'email' => $utente->email,
            'phone' => (string) $utente->phone,
            'role' => $utente->role->value,
            'store_id' => $utente->store_id,
            'is_active' => $utente->is_active,
        ];
        $this->password = '';
    }

    public function nuovo(): void
    {
        $this->modificaId = null;
        $this->form = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'role' => 'CAPO_REPARTO', 'store_id' => null, 'is_active' => true];
        $this->password = '';
    }

    public function salva(): void
    {
        $this->authorize('gestire-anagrafiche');

        $dati = $this->validate([
            'form.first_name' => ['required', 'string', 'max:80'],
            'form.last_name' => ['required', 'string', 'max:80'],
            'form.email' => ['required', 'email', 'max:190', 'unique:users,email'.($this->modificaId ? ','.$this->modificaId : '')],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.role' => ['required', 'in:BUYER,TECNICO,CAPO_REPARTO'],
            // Un Capo Reparto deve sempre avere un punto vendita (assunzione A1).
            'form.store_id' => ['nullable', 'integer', 'exists:stores,id', 'required_if:form.role,CAPO_REPARTO'],
            'form.is_active' => ['boolean'],
            'password' => [$this->modificaId ? 'nullable' : 'required', PasswordRule::min(10)->letters()->numbers()],
        ], [
            'form.store_id.required_if' => 'Un Capo Reparto deve essere associato a un punto vendita.',
        ]);

        $utente = $this->modificaId ? User::findOrFail($this->modificaId) : new User;
        $utente->fill($dati['form']);

        if ($this->password !== '') {
            $utente->password = $this->password;
            $utente->must_change_password = true;
        }

        if ($utente->role !== Role::CAPO_REPARTO) {
            $utente->store_id = null;
        }

        $utente->save();

        app(AuditService::class)->log(
            $this->modificaId ? 'user.updated' : 'user.created', $utente,
            ['email' => $utente->email, 'ruolo' => $utente->role->value],
        );

        $this->nuovo();
        $this->dispatch('toast', messaggio: 'Utente salvato.');
    }

    public function render()
    {
        $query = User::with('store')->orderBy('last_name');

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->ricerca}%")
                    ->orWhere('last_name', 'like', "%{$this->ricerca}%")
                    ->orWhere('email', 'like', "%{$this->ricerca}%");
            });
        }

        return view('livewire.tecnico.anagrafiche.utenti', [
            'elenco' => $query->paginate(15),
            'ruoli' => Role::cases(),
            'puntiVendita' => Store::orderBy('code')->get(),
        ]);
    }
}
