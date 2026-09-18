<x-layouts.auth title="Cambio password">
    <h2 class="text-lg font-semibold text-slate-900">Cambia la password</h2>
    <p class="mt-1 text-sm text-slate-600">Per continuare devi sostituire la password iniziale.</p>

    <form method="POST" action="{{ route('password.change.update') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="current_password" class="label">Password attuale</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
            @error('current_password') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Nuova password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
            <p class="help">Almeno 10 caratteri, con lettere e numeri.</p>
            @error('password') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Conferma nuova password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Aggiorna password</button>
    </form>
</x-layouts.auth>
