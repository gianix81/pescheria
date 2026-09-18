<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Creazione del primo Super Admin dal browser.
 *
 * Serve dove aprire una shell non è pratico o non è possibile: si imposta
 * SETUP_TOKEN fra le variabili d'ambiente, si apre /setup/<token> e si crea
 * l'account. Nessun altro modo di entrare è richiesto.
 *
 * La pagina esiste solo se ricorrono TUTTE queste condizioni:
 *  - SETUP_TOKEN è impostato;
 *  - il token nell'indirizzo corrisponde;
 *  - non esiste ancora nessun Super Admin attivo.
 *
 * Appena il primo Super Admin è creato la pagina smette di esistere da sola:
 * non resta una porta aperta da ricordarsi di chiudere. In ogni altro caso
 * risponde 404, senza rivelare che l'indirizzo esiste.
 */
class SetupController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function show(string $token)
    {
        $this->assertDisponibile($token);

        return view('auth.setup', ['token' => $token]);
    }

    public function store(Request $request, string $token)
    {
        $this->assertDisponibile($token);

        $dati = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $utente = new User;
        $utente->fill([
            'first_name' => $dati['first_name'],
            'last_name' => $dati['last_name'],
            'email' => $dati['email'],
            'role' => Role::ADMIN,
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $utente->password = $dati['password'];
        $utente->save();

        $this->audit->log('setup.admin_created', $utente, ['email' => $utente->email], $utente);

        return redirect()->route('login')->with('status',
            'Super Admin creato: ora puoi accedere. La pagina di configurazione non è più raggiungibile.');
    }

    private function assertDisponibile(string $token): void
    {
        $atteso = (string) config('pescheria.setup_token');

        abort_if($atteso === '', 404);
        abort_unless(hash_equals($atteso, $token), 404);

        // Già configurata: la porta si chiude da sé.
        abort_if(
            User::where('role', Role::ADMIN)->where('is_active', true)->exists(),
            404,
        );
    }
}
