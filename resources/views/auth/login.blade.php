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

    @if (config('pescheria.demo.enabled'))
        <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 px-3 py-3 text-sm text-amber-900">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Ambiente dimostrativo</p>
            <p class="mt-1">I dati si azzerano a ogni riavvio. Accedi con uno di questi profili:</p>
            <ul class="mt-2 space-y-1 font-mono text-xs">
                <li><button type="button" class="underline" x-on:click="$refs.email.value = 'buyer@pescheria.local'; $refs.password.value = 'Pescheria2026!'">buyer@pescheria.local</button></li>
                <li><button type="button" class="underline" x-on:click="$refs.email.value = 'tecnico@pescheria.local'; $refs.password.value = 'Pescheria2026!'">tecnico@pescheria.local</button></li>
                <li><button type="button" class="underline" x-on:click="$refs.email.value = 'cr1@pescheria.local'; $refs.password.value = 'Pescheria2026!'">cr1@pescheria.local</button> (Capo Reparto)</li>
            </ul>
            <p class="mt-2 text-xs">Password per tutti: <strong>Pescheria2026!</strong> — tocca un indirizzo per compilare il modulo.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" x-data>
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="username" required autofocus
                   x-ref="email" value="{{ old('email') }}" class="input @error('email') input-error @enderror">
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required x-ref="password" class="input">
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
