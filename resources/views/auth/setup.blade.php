<x-layouts.auth title="Primo accesso">
    <h2 class="text-lg font-semibold text-slate-900">Crea il Super Admin</h2>
    <p class="mt-1 text-sm text-slate-600">
        Questa pagina compare solo finché non esiste un Super Admin attivo. Dopo la creazione
        smette di essere raggiungibile.
    </p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-900" role="alert">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $errore)
                    <li>{{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('setup.store', $token) }}" class="mt-6 space-y-4">
        @csrf

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="first_name" class="label">Nome</label>
                <input id="first_name" name="first_name" required autofocus value="{{ old('first_name') }}" class="input">
            </div>
            <div>
                <label for="last_name" class="label">Cognome</label>
                <input id="last_name" name="last_name" required value="{{ old('last_name') }}" class="input">
            </div>
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}" class="input">
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
            <p class="help">Almeno 10 caratteri, con lettere e numeri.</p>
        </div>

        <div>
            <label for="password_confirmation" class="label">Conferma password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Crea Super Admin</button>
    </form>
</x-layouts.auth>
