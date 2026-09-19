@props(['titolo', 'azione' => null, 'urlAzione' => null, 'urgente' => false])

{{--
    Riga d'apertura di ogni pagina di ruolo: dice in una frase che cosa si sta
    guardando e qual è la prossima cosa da fare. Sta in alto e non occupa più di
    una riga su telefono, così l'azione principale è raggiungibile senza scorrere.
--}}
<div @class([
    'flex flex-wrap items-center justify-between gap-3 rounded-xl border px-4 py-3',
    'border-amber-300 bg-amber-50' => $urgente,
    'border-slate-200 bg-white' => ! $urgente,
])>
    <p @class([
        'text-sm font-semibold leading-snug',
        'text-amber-900' => $urgente,
        'text-slate-800' => ! $urgente,
    ])>
        {{ $titolo }}
    </p>

    @if ($azione && $urlAzione)
        <a href="{{ $urlAzione }}" class="btn-primary shrink-0 px-3 py-2 text-sm">{{ $azione }}</a>
    @endif
</div>
