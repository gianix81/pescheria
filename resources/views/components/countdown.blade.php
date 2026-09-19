@props(['scadenza', 'etichetta' => 'Scade tra'])

@php
    $scaduta = $scadenza->lessThanOrEqualTo(now());
    $millisecondi = max(0, ($scadenza->getTimestamp() - now()->getTimestamp()) * 1000);
    $urgente = ! $scaduta && $scadenza->diffInMinutes(now(), true) <= 120;

    // Il testo completo si compone qui, non concatenando etichetta e valore:
    // a termine passato "Scade tra" non ha senso e va omesso.
    $testoIniziale = $scaduta
        ? 'Scaduta'
        : trim($etichetta.' '.\App\Support\Format::countdown($scadenza));
@endphp

{{-- Conteggio informativo: la scadenza vera è sempre verificata dal server. --}}
<span
    x-data="{
        restanti: {{ $millisecondi }},
        etichetta: @js($etichetta),
        testo: @js($testoIniziale),
        aggiorna() {
            if (this.restanti <= 0) { this.testo = 'Scaduta'; return }
            let s = Math.floor(this.restanti / 1000)
            let g = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60)
            let durata = g > 0 ? `${g}g ${h}h` : (h > 0 ? `${h}h ${m}m` : `${m} min`)
            this.testo = (this.etichetta ? this.etichetta + ' ' : '') + durata
        }
    }"
    x-init="aggiorna(); setInterval(() => { restanti -= 1000; aggiorna() }, 1000)"
    {{ $attributes->merge(['class' => 'badge '.match (true) {
        $scaduta => 'bg-slate-200 text-slate-700 ring-slate-400',
        $urgente => 'bg-amber-50 text-amber-900 ring-amber-300',
        default => 'bg-slate-100 text-slate-700 ring-slate-300',
    }]) }}
>
    <span aria-hidden="true">⏱</span>
    <span x-text="testo">{{ $testoIniziale }}</span>
</span>
