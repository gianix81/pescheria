<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['scadenza', 'etichetta' => 'Scade tra']));

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

foreach (array_filter((['scadenza', 'etichetta' => 'Scade tra']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $scaduta = $scadenza->lessThanOrEqualTo(now());
    $millisecondi = max(0, ($scadenza->getTimestamp() - now()->getTimestamp()) * 1000);
    $urgente = ! $scaduta && $scadenza->diffInMinutes(now(), true) <= 120;

    // Il testo completo si compone qui, non concatenando etichetta e valore:
    // a termine passato "Scade tra" non ha senso e va omesso.
    $testoIniziale = $scaduta
        ? 'Scaduta'
        : trim($etichetta.' '.\App\Support\Format::countdown($scadenza));
?>


<span
    x-data="{
        restanti: <?php echo e($millisecondi); ?>,
        etichetta: <?php echo \Illuminate\Support\Js::from($etichetta)->toHtml() ?>,
        testo: <?php echo \Illuminate\Support\Js::from($testoIniziale)->toHtml() ?>,
        aggiorna() {
            if (this.restanti <= 0) { this.testo = 'Scaduta'; return }
            let s = Math.floor(this.restanti / 1000)
            let g = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60)
            let durata = g > 0 ? `${g}g ${h}h` : (h > 0 ? `${h}h ${m}m` : `${m} min`)
            this.testo = (this.etichetta ? this.etichetta + ' ' : '') + durata
        }
    }"
    x-init="aggiorna(); setInterval(() => { restanti -= 1000; aggiorna() }, 1000)"
    <?php echo e($attributes->merge(['class' => 'badge '.match (true) {
        $scaduta => 'bg-slate-200 text-slate-700 ring-slate-400',
        $urgente => 'bg-amber-50 text-amber-900 ring-amber-300',
        default => 'bg-slate-100 text-slate-700 ring-slate-300',
    }])); ?>

>
    <span aria-hidden="true">⏱</span>
    <span x-text="testo"><?php echo e($testoIniziale); ?></span>
</span>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/countdown.blade.php ENDPATH**/ ?>