<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function showRequest()
    {
        return view('auth.password-request');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Messaggio neutro: non rivelare se l'email esiste.
        return back()->with('status', $status === Password::RESET_LINK_SENT
            ? 'Se l\'indirizzo è registrato riceverai le istruzioni per reimpostare la password.'
            : 'Se l\'indirizzo è registrato riceverai le istruzioni per reimpostare la password.');
    }

    public function showReset(string $token)
    {
        return view('auth.password-reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                $this->audit->log('auth.password_reset', $user, [], $user);
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password aggiornata: ora puoi accedere.')
            : back()->withErrors(['email' => 'Il link di reimpostazione non è valido o è scaduto.']);
    }
}
