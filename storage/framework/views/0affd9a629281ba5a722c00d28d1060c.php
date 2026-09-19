<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['opportunita']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['opportunita']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $netto = $opportunita->netSalePrice();
?>
<dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-4">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Acquisto</dt>
        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::money($opportunita->purchase_price)); ?>/kg</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendita (IVA incl.)</dt>
        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::money($opportunita->sale_price_gross)); ?>/kg</dd>
        <dd class="text-xs text-slate-500">netto <?php echo e(\App\Support\Format::money($netto)); ?> · IVA <?php echo e(\App\Support\Format::percent($opportunita->vat_rate, 0)); ?></dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500" title="Utile diviso il prezzo di acquisto: è il valore che nel gruppo WhatsApp veniva chiamato impropriamente margine.">
            Ricarico ⓘ
        </dt>
        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::percent($opportunita->markup_percent)); ?></dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500" title="Utile diviso il prezzo di vendita netto IVA: è il margine commerciale.">
            Margine ⓘ
        </dt>
        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::percent($opportunita->margin_percent)); ?></dd>
    </div>
</dl>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunita->pricing_overridden): ?>
    <p class="mt-2 text-xs text-amber-800">
        <span aria-hidden="true">⚠</span> Valori forzati manualmente: <?php echo e($opportunita->pricing_override_reason); ?>

    </p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/prezzo-box.blade.php ENDPATH**/ ?>