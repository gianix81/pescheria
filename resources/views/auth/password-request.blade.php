<x-layouts.auth title="Password dimenticata">
    <h2 class="text-lg font-semibold text-slate-900">Password dimenticata</h2>
    <p class="mt-1 text-sm text-slate-600">Inserisci la tua email: riceverai un link per reimpostarla.</p>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-900" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" class="input">
            @error('email') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary w-full">Invia istruzioni</button>
        <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-laguna-600 underline underline-offset-2">Torna all'accesso</a>
    </form>
</x-layouts.auth>
