<x-layouts.auth title="Nuova password">
    <h2 class="text-lg font-semibold text-slate-900">Imposta una nuova password</h2>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" required value="{{ old('email', request('email')) }}" class="input">
            @error('email') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Nuova password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
            <p class="help">Almeno 10 caratteri, con lettere e numeri.</p>
            @error('password') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Conferma password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Salva password</button>
    </form>
</x-layouts.auth>
