<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['titolo', 'azione' => null, 'urlAzione' => null, 'urgente' => false]));

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

foreach (array_filter((['titolo', 'azione' => null, 'urlAzione' => null, 'urgente' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'flex flex-wrap items-center justify-between gap-3 rounded-xl border px-4 py-3',
    'border-amber-300 bg-amber-50' => $urgente,
    'border-slate-200 bg-white' => ! $urgente,
]); ?>">
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'text-sm font-semibold leading-snug',
        'text-amber-900' => $urgente,
        'text-slate-800' => ! $urgente,
    ]); ?>">
        <?php echo e($titolo); ?>

    </p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($azione && $urlAzione): ?>
        <a href="<?php echo e($urlAzione); ?>" class="btn-primary shrink-0 px-3 py-2 text-sm"><?php echo e($azione); ?></a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/obiettivo.blade.php ENDPATH**/ ?>