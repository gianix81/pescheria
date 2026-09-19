<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['stato', 'compatto' => false]));

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

foreach (array_filter((['stato', 'compatto' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<span <?php echo e($attributes->merge(['class' => 'badge '.$stato->badgeClasses()])); ?> title="<?php echo e($stato->label()); ?>">
    <span aria-hidden="true"><?php echo e($stato->icon()); ?></span>
    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['sr-only' => $compatto]); ?>"><?php echo e($compatto ? $stato->label() : $stato->shortLabel()); ?></span>
</span>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/badge-risposta.blade.php ENDPATH**/ ?>