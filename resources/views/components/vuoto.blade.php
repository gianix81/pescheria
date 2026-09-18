@props(['titolo', 'descrizione' => null, 'icona' => '🗂'])
<div {{ $attributes->merge(['class' => 'card flex flex-col items-center justify-center gap-2 px-6 py-12 text-center']) }}>
    <span class="text-3xl" aria-hidden="true">{{ $icona }}</span>
    <p class="text-base font-semibold text-slate-800">{{ $titolo }}</p>
    @if ($descrizione)
        <p class="max-w-md text-sm text-slate-600">{{ $descrizione }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
