<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['etichetta', 'valore', 'nota' => null, 'url' => null, 'evidenzia' => false]));

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

foreach (array_filter((['etichetta', 'valore', 'nota' => null, 'url' => null, 'evidenzia' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<?php
    $classi = 'card flex flex-col justify-between px-3 py-2.5 '
        .($evidenzia ? 'border-amber-300 bg-amber-50' : '');
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($url): ?>
    <a href="<?php echo e($url); ?>" class="<?php echo e($classi); ?> transition hover:border-mare-300">
        <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($etichetta); ?></p>
        <p class="text-2xl font-bold leading-tight <?php echo e($evidenzia ? 'text-amber-900' : 'text-slate-900'); ?>"><?php echo e($valore); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nota): ?><p class="truncate text-[11px] text-slate-500"><?php echo e($nota); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
<?php else: ?>
    <div class="<?php echo e($classi); ?>">
        <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($etichetta); ?></p>
        <p class="text-2xl font-bold leading-tight <?php echo e($evidenzia ? 'text-amber-900' : 'text-slate-900'); ?>"><?php echo e($valore); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nota): ?><p class="truncate text-[11px] text-slate-500"><?php echo e($nota); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/riquadro-numero.blade.php ENDPATH**/ ?>