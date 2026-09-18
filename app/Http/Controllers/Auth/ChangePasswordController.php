<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/** Cambio password obbligatorio al primo accesso (credenziali demo in produzione). */
class ChangePasswordController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function show()
    {
        return view('auth.password-change');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'La password attuale non è corretta.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'must_change_password' => false,
        ])->save();

        $this->audit->log('auth.password_changed', $user, [], $user);

        return redirect()->route('home')->with('status', 'Password aggiornata.');
    }
}
