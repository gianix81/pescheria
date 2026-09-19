<?php

namespace App\Livewire\Tecnico\Anagrafiche;

use App\Enums\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Gestione dei profili.
 *
 * Creazione e modifica sono consentite a Tecnico e Super Admin; le azioni
 * irreversibili o delicate — eliminazione, ripristino, reimpostazione della
 * password — solo al Super Admin, e con dei paracadute: non ci si può
 * disattivare o eliminare da soli, e l'ultimo Super Admin attivo non può
 * essere rimosso, altrimenti l'applicazione resterebbe senza nessuno in grado
 * di rientrare.
 */
#[Layout('components.layouts.app')]
#[Title('Utenti')]
class Utenti extends Component
{
    use WithPagination;

    public ?int $modificaId = null;

    public string $ricerca = '';

    public string $filtroRuolo = '';

    public bool $mostraEliminati = false;

    public array $form = [
        'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
        'role' => 'CAPO_REPARTO', 'store_id' => null, 'is_active' => true,
    ];

    public string $password = '';

    /** Password generata da mostrare una sola volta dopo un reset. */
    public ?string $passwordGenerata = null;

    public ?int $confermaEliminazione = null;

    public function mount(): void
    {
        $this->authorize('gestire-anagrafiche');
    }

    // ---------------------------------------------------------------- lettura

    public function modifica(int $id): void
    {
        $utente = $this->trovaUtente($id);

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
        $this->passwordGenerata = null;
        $this->resetErrorBag();
    }

    public function nuovo(): void
    {
        $this->modificaId = null;
        $this->form = [
            'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
            'role' => 'CAPO_REPARTO', 'store_id' => null, 'is_active' => true,
        ];
        $this->password = '';
        $this->passwordGenerata = null;
        $this->resetErrorBag();
    }

    // ---------------------------------------------------------------- scrittura

    public function salva(): void
    {
        $this->authorize('gestire-anagrafiche');

        $dati = $this->validate([
            'form.first_name' => ['required', 'string', 'max:80'],
            'form.last_name' => ['required', 'string', 'max:80'],
            'form.email' => ['required', 'email', 'max:190', 'unique:users,email'.($this->modificaId ? ','.$this->modificaId : '')],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.role' => ['required', 'in:'.implode(',', array_column(Role::cases(), 'value'))],
            'form.store_id' => ['nullable', 'integer', 'exists:stores,id', 'required_if:form.role,CAPO_REPARTO'],
            'form.is_active' => ['boolean'],
            'password' => [$this->modificaId ? 'nullable' : 'required', PasswordRule::min(10)->letters()->numbers()],
        ], [
            'form.store_id.required_if' => 'Scegli il punto vendita per cui questo utente ordina.',
        ]);

        $utente = $this->modificaId ? $this->trovaUtente($this->modificaId) : new User;
        $ruoloRichiesto = Role::from($dati['form']['role']);

        // Solo il Super Admin può creare o promuovere altri Super Admin.
        if ($ruoloRichiesto === Role::ADMIN && ! auth()->user()->isAdmin()) {
            $this->addError('form.role', 'Solo un Super Admin può assegnare il ruolo Super Admin.');

            return;
        }

        if ($this->modificaId && ! $this->puoRestareSenzaAdmin($utente, $ruoloRichiesto, (bool) $dati['form']['is_active'])) {
            return;
        }

        $utente->fill($dati['form']);

        if ($this->password !== '') {
            $utente->password = $this->password;
            $utente->must_change_password = true;
        }

        if (! $utente->role->requiresStore()) {
            $utente->store_id = null;
        }

        $utente->save();

        app(AuditService::class)->log(
            $this->modificaId ? 'user.updated' : 'user.created',
            $utente,
            ['email' => $utente->email, 'ruolo' => $utente->role->value, 'attivo' => $utente->is_active],
        );

        $this->nuovo();
        $this->dispatch('toast', messaggio: 'Profilo salvato.');
    }

    public function attivaDisattiva(int $id): void
    {
        $this->authorize('gestire-anagrafiche');

        $utente = $this->trovaUtente($id);

        if ($utente->is_active && ! $this->puoRestareSenzaAdmin($utente, $utente->role, false)) {
            return;
        }

        $utente->forceFill(['is_active' => ! $utente->is_active])->save();

        app(AuditService::class)->log('user.toggled', $utente, [
            'email' => $utente->email,
            'attivo' => $utente->is_active,
        ]);

        $this->dispatch('toast', messaggio: $utente->is_active ? 'Profilo riattivato.' : 'Profilo disattivato.');
    }

    // ---------------------------------------------------- azioni da Super Admin

    public function reimpostaPassword(int $id): void
    {
        $this->authorize('gestire-account');

        $utente = $this->trovaUtente($id);
        $nuova = Str::password(14, symbols: false);

        $utente->forceFill([
            'password' => $nuova,
            'must_change_password' => true,
        ])->save();

        // La password non finisce nell'audit: si registra solo che è avvenuto.
        app(AuditService::class)->log('user.password_reset', $utente, ['email' => $utente->email]);

        $this->passwordGenerata = $nuova;
        $this->modificaId = $utente->id;

        $this->dispatch('toast', messaggio: 'Password reimpostata: copiala, non sarà più visibile.');
    }

    public function chiediEliminazione(int $id): void
    {
        $this->authorize('gestire-account');

        $this->confermaEliminazione = $id;
    }

    public function elimina(): void
    {
        $this->authorize('gestire-account');

        $utente = $this->trovaUtente((int) $this->confermaEliminazione);

        if ($utente->id === auth()->id()) {
            $this->addError('eliminazione', 'Non puoi eliminare il tuo stesso profilo.');

            return;
        }

        if (! $this->puoRestareSenzaAdmin($utente, $utente->role, false)) {
            return;
        }

        $utente->delete();      // soft delete: lo storico degli ordini resta leggibile

        app(AuditService::class)->log('user.deleted', $utente, [
            'email' => $utente->email,
            'ruolo' => $utente->role->value,
        ]);

        $this->confermaEliminazione = null;
        $this->nuovo();
        $this->dispatch('toast', messaggio: 'Profilo eliminato. Puoi ripristinarlo dagli eliminati.');
    }

    public function ripristina(int $id): void
    {
        $this->authorize('gestire-account');

        $utente = User::withTrashed()->findOrFail($id);
        $utente->restore();

        app(AuditService::class)->log('user.restored', $utente, ['email' => $utente->email]);

        $this->dispatch('toast', messaggio: 'Profilo ripristinato.');
    }

    // ------------------------------------------------------------------ interne

    private function trovaUtente(int $id): User
    {
        return User::withTrashed()->findOrFail($id);
    }

    /**
     * Impedisce di rimuovere, disattivare o declassare l'ultimo Super Admin
     * attivo: senza, nessuno potrebbe più gestire i profili.
     */
    private function puoRestareSenzaAdmin(User $utente, Role $ruoloFinale, bool $restaAttivo): bool
    {
        $perdeIPoteri = $utente->role === Role::ADMIN
            && ($ruoloFinale !== Role::ADMIN || ! $restaAttivo);

        if (! $perdeIPoteri) {
            return true;
        }

        $altriAdmin = User::where('role', Role::ADMIN)
            ->where('is_active', true)
            ->whereKeyNot($utente->id)
            ->count();

        if ($altriAdmin === 0) {
            $this->addError('eliminazione', 'Questo è l\'ultimo Super Admin attivo: creane un altro prima di rimuoverlo.');

            return false;
        }

        return true;
    }

    public function render()
    {
        $query = $this->mostraEliminati ? User::onlyTrashed() : User::query();

        $query->with('store')->orderBy('role')->orderBy('last_name');

        if ($this->filtroRuolo !== '') {
            $query->where('role', $this->filtroRuolo);
        }

        if ($this->ricerca !== '') {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->ricerca}%")
                    ->orWhere('last_name', 'like', "%{$this->ricerca}%")
                    ->orWhere('email', 'like', "%{$this->ricerca}%");
            });
        }

        return view('livewire.tecnico.anagrafiche.utenti', [
            'elenco' => $query->paginate(15),
            'ruoli' => Role::assegnabili(),
            'puntiVendita' => Store::orderBy('code')->get(),
            'puoGestireAccount' => auth()->user()->can('gestire-account'),
            'conteggiRuolo' => User::selectRaw('role, count(*) as totale')->groupBy('role')->pluck('totale', 'role'),
        ]);
    }
}
