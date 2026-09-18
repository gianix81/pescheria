@props(['scadenza', 'etichetta' => 'Scade tra'])
@php
    $millisecondi = max(0, $scadenza->getTimestamp() * 1000 - now()->getTimestamp() * 1000);
    $urgente = $scadenza->diffInMinutes(now(), true) <= 120 && $scadenza->isFuture();
@endphp
{{-- Il countdown è informativo: la scadenza reale è sempre verificata dal server. --}}
<span
    x-data="{
        restanti: {{ $millisecondi }},
        testo: '',
        aggiorna() {
            if (this.restanti <= 0) { this.testo = 'Scaduta'; return }
            let s = Math.floor(this.restanti / 1000)
            let g = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60)
            this.testo = g > 0 ? `${g}g ${h}h` : (h > 0 ? `${h}h ${m}m` : `${m} min`)
        }
    }"
    x-init="aggiorna(); setInterval(() => { restanti -= 1000; aggiorna() }, 1000)"
    {{ $attributes->merge(['class' => 'badge '.($urgente ? 'bg-amber-50 text-amber-900 ring-amber-300' : 'bg-slate-100 text-slate-700 ring-slate-300')]) }}
>
    <span aria-hidden="true">⏱</span>
    <span>{{ $etichetta }} <span x-text="testo">{{ \App\Support\Format::countdown($scadenza) }}</span></span>
</span>
