<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], ['email' => 'email', 'password' => 'password']);

        // Un account disattivato non può accedere, anche con password corretta.
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $this->audit->log('auth.login_failed', null, ['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => 'Credenziali non valide oppure account disattivato.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->save();

        $this->audit->log('auth.login', $user, ['ruolo' => $user->role->value], $user);

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request)
    {
        $this->audit->log('auth.logout', $request->user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
