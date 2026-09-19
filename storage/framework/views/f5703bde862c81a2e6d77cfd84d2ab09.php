<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'testo',
    'numero' => null,
    'etichetta' => 'Condividi su WhatsApp',
    'descrizione' => null,
    'variante' => 'secondario',
]));

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

foreach (array_filter(([
    'testo',
    'numero' => null,
    'etichetta' => 'Condividi su WhatsApp',
    'descrizione' => null,
    'variante' => 'secondario',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->merge(['class' => 'space-y-2'])); ?> x-data="{ copiato: false }">
    <div class="flex flex-wrap gap-2">
        <a href="<?php echo e(\App\Support\WhatsApp::link($testo, $numero)); ?>" target="_blank" rel="noopener"
           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
               'btn',
               'bg-[#25D366] text-white hover:bg-[#1da851]' => $variante === 'principale',
               'border border-[#25D366] bg-white text-[#0b7a3b] hover:bg-emerald-50' => $variante !== 'principale',
           ]); ?>">
            <span aria-hidden="true">✆</span> <?php echo e($etichetta); ?>

        </a>

        <button type="button" class="btn-ghost"
                x-on:click="navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from($testo)->toHtml() ?>).then(() => { copiato = true; setTimeout(() => copiato = false, 2500) })">
            <span x-show="!copiato">Copia testo</span>
            <span x-show="copiato" x-cloak>Copiato ✓</span>
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($descrizione): ?>
        <p class="help"><?php echo e($descrizione); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/condividi-whatsapp.blade.php ENDPATH**/ ?>