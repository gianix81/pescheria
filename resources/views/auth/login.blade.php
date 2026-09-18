<x-layouts.auth title="Accesso">
    <h2 class="text-lg font-semibold text-slate-900">Accedi</h2>
    <p class="mt-1 text-sm text-slate-600">Usa le credenziali fornite dall'ufficio acquisti.</p>

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-900" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-900" role="alert">
            <p class="font-semibold">Non è stato possibile accedere</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $errore)
                    <li>{{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="username" required autofocus
                   value="{{ old('email') }}" class="input @error('email') input-error @enderror">
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="input">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                Ricordami
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-laguna-600 underline underline-offset-2">Password dimenticata?</a>
        </div>

        <button type="submit" class="btn-primary w-full">Accedi</button>
    </form>
</x-layouts.auth>
