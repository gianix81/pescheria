@props(['etichetta', 'valore', 'nota' => null, 'url' => null, 'evidenzia' => false])

{{--
    Numero compatto. Su telefono ne stanno due per riga invece di uno, così i
    conteggi occupano metà schermata invece di quattro.
--}}
@php
    $classi = 'card flex flex-col justify-between px-3 py-2.5 '
        .($evidenzia ? 'border-amber-300 bg-amber-50' : '');
@endphp

@if ($url)
    <a href="{{ $url }}" class="{{ $classi }} transition hover:border-mare-300">
        <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $etichetta }}</p>
        <p class="text-2xl font-bold leading-tight {{ $evidenzia ? 'text-amber-900' : 'text-slate-900' }}">{{ $valore }}</p>
        @if ($nota)<p class="truncate text-[11px] text-slate-500">{{ $nota }}</p>@endif
    </a>
@else
    <div class="{{ $classi }}">
        <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $etichetta }}</p>
        <p class="text-2xl font-bold leading-tight {{ $evidenzia ? 'text-amber-900' : 'text-slate-900' }}">{{ $valore }}</p>
        @if ($nota)<p class="truncate text-[11px] text-slate-500">{{ $nota }}</p>@endif
    </div>
@endif
