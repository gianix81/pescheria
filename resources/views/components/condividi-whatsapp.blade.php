@props([
    'testo',
    'numero' => null,
    'etichetta' => 'Condividi su WhatsApp',
    'descrizione' => null,
    'variante' => 'secondario',
])

{{--
    Apre WhatsApp con il messaggio già scritto. Con un numero apre direttamente
    quella conversazione; senza, si sceglie la chat o il gruppo e si invia.
    Funziona con WhatsApp personale: nessuna API, nessuna configurazione.
--}}
<div {{ $attributes->merge(['class' => 'space-y-2']) }} x-data="{ copiato: false }">
    <div class="flex flex-wrap gap-2">
        <a href="{{ \App\Support\WhatsApp::link($testo, $numero) }}" target="_blank" rel="noopener"
           @class([
               'btn',
               'bg-[#25D366] text-white hover:bg-[#1da851]' => $variante === 'principale',
               'border border-[#25D366] bg-white text-[#0b7a3b] hover:bg-emerald-50' => $variante !== 'principale',
           ])>
            <span aria-hidden="true">✆</span> {{ $etichetta }}
        </a>

        <button type="button" class="btn-ghost"
                x-on:click="navigator.clipboard.writeText(@js($testo)).then(() => { copiato = true; setTimeout(() => copiato = false, 2500) })">
            <span x-show="!copiato">Copia testo</span>
            <span x-show="copiato" x-cloak>Copiato ✓</span>
        </button>
    </div>

    @if ($descrizione)
        <p class="help">{{ $descrizione }}</p>
    @endif
</div>
