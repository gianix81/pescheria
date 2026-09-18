<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Ordini pescheria' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-guscio font-sans text-slate-900 antialiased">
@php
    $user = auth()->user();
    $nonLette = $user ? \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;
@endphp

<a href="#contenuto" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-2 focus:rounded focus:bg-mare-700 focus:px-4 focus:py-2 focus:text-white">
    Vai al contenuto
</a>

<div class="min-h-full lg:flex" x-data="{ sidebar: false }">

    {{-- ------------------------------------------------------------ Sidebar --}}
    <div x-show="sidebar" x-cloak class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebar = false" aria-hidden="true"></div>

    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 shrink-0 -translate-x-full overflow-y-auto bg-mare-700 text-mare-50 transition-transform lg:static lg:translate-x-0"
        :class="sidebar && 'translate-x-0'"
        aria-label="Navigazione principale"
    >
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <span class="flex size-9 items-center justify-center rounded-lg bg-laguna-500 text-lg font-bold">P</span>
            <div class="leading-tight">
                <p class="text-sm font-bold">{{ config('app.name') }}</p>
                <p class="text-xs text-mare-100">Reparti pescheria</p>
            </div>
            <button type="button" class="ml-auto rounded p-2 text-mare-100 lg:hidden" @click="sidebar = false" aria-label="Chiudi menu">✕</button>
        </div>

        <nav class="space-y-1 px-3 py-4 text-sm">
            @foreach (\App\Support\Navigation::for($user) as $voce)
                <a href="{{ $voce['url'] }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                       'bg-white/15 text-white' => $voce['active'],
                       'text-mare-100 hover:bg-white/10 hover:text-white' => ! $voce['active'],
                   ])
                   @if($voce['active']) aria-current="page" @endif>
                    <span aria-hidden="true" class="w-5 text-center">{{ $voce['icon'] }}</span>
                    <span>{{ $voce['label'] }}</span>
                    @if (($voce['badge'] ?? 0) > 0)
                        <span class="ml-auto rounded-full bg-laguna-500 px-2 py-0.5 text-xs font-bold text-white">{{ $voce['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-auto border-t border-white/10 px-5 py-4 text-sm">
            <p class="font-semibold text-white">{{ $user?->full_name }}</p>
            <p class="text-xs text-mare-100">
                {{ $user?->role->label() }}@if($user?->store) · {{ $user->store->code }} @endif
            </p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="text-xs font-semibold text-laguna-100 underline underline-offset-2 hover:text-white">
                    Esci
                </button>
            </form>
        </div>
    </aside>

    {{-- ------------------------------------------------------------ Contenuto --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebar = true" aria-label="Apri menu">☰</button>

            <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">{{ $title ?? 'Ordini pescheria' }}</h1>

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('notifiche.index') }}" class="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="Notifiche ({{ $nonLette }} non lette)">
                    <span aria-hidden="true">🔔</span>
                    @if ($nonLette > 0)
                        <span class="absolute -right-0.5 -top-0.5 rounded-full bg-rose-600 px-1.5 text-[10px] font-bold text-white">{{ $nonLette }}</span>
                    @endif
                </a>
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold leading-tight text-slate-800">{{ $user?->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $user?->role->label() }}</p>
                </div>
            </div>
        </header>

        @if (config('pescheria.demo.enabled'))
            <div class="flex items-start gap-2 border-b border-amber-300 bg-amber-100 px-4 py-2.5 text-sm text-amber-900 sm:px-6" role="status">
                <span aria-hidden="true">⚠</span>
                <p>
                    <strong>Ambiente dimostrativo.</strong>
                    I dati sono temporanei e si azzerano a ogni riavvio. Non usarlo per ordini reali:
                    il controllo sulla disponibilità limitata qui non è garantito.
                </p>
            </div>
        @endif

        <main id="contenuto" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    <span aria-hidden="true">✓</span><span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('errore'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
                    <span aria-hidden="true">⚠</span><span>{{ session('errore') }}</span>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 px-4 py-4 text-xs text-slate-500 sm:px-6">
            {{ config('app.name') }} — orari in {{ config('app.display_timezone') }}.
        </footer>
    </div>
</div>

@persist('toast')
<div x-data="{ messaggio: '', mostra: false }"
     x-on:toast.window="messaggio = $event.detail.messaggio; mostra = true; setTimeout(() => mostra = false, 4000)"
     x-show="mostra" x-cloak
     class="fixed bottom-4 left-1/2 z-50 -translate-x-1/2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-medium text-white shadow-lg"
     role="status" aria-live="polite">
    <span x-text="messaggio"></span>
</div>
@endpersist
</body>
</html>
