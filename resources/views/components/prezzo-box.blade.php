@props(['opportunita'])
@php
    $netto = $opportunita->netSalePrice();
@endphp
<dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-4">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Acquisto</dt>
        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::money($opportunita->purchase_price) }}/kg</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendita (IVA incl.)</dt>
        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::money($opportunita->sale_price_gross) }}/kg</dd>
        <dd class="text-xs text-slate-500">netto {{ \App\Support\Format::money($netto) }} · IVA {{ \App\Support\Format::percent($opportunita->vat_rate, 0) }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500" title="Utile diviso il prezzo di acquisto: è il valore che nel gruppo WhatsApp veniva chiamato impropriamente margine.">
            Ricarico ⓘ
        </dt>
        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::percent($opportunita->markup_percent) }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500" title="Utile diviso il prezzo di vendita netto IVA: è il margine commerciale.">
            Margine ⓘ
        </dt>
        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::percent($opportunita->margin_percent) }}</dd>
    </div>
</dl>
@if ($opportunita->pricing_overridden)
    <p class="mt-2 text-xs text-amber-800">
        <span aria-hidden="true">⚠</span> Valori forzati manualmente: {{ $opportunita->pricing_override_reason }}
    </p>
@endif
