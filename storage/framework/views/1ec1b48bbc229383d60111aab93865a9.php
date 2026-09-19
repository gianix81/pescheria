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
<?php $media = $opportunita->media; ?>

<div x-data="{ attivo: 0 }" class="space-y-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media->isEmpty()): ?>
        <div class="flex aspect-[4/3] items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-500">
            Nessun contenuto multimediale
        </div>
    <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div x-show="attivo === <?php echo e($indice); ?>" <?php if($indice > 0): ?> x-cloak <?php endif; ?>>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $file->esiste()): ?>
                    
                    <div class="flex aspect-[4/3] flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                        <span class="text-3xl" aria-hidden="true">🖼</span>
                        <p class="text-sm font-semibold text-slate-700">
                            <?php echo e($file->type->label()); ?> non più disponibile
                        </p>
                        <p class="max-w-sm text-xs text-slate-500">
                            Il file è stato caricato il <?php echo e(\App\Support\Format::date($file->created_at)); ?>

                            ma non si trova più sul disco. Ricaricalo dalla modifica dell'opportunità.
                        </p>
                    </div>
                <?php elseif($file->isVideo()): ?>
                    
                    <video
                        class="mx-auto max-h-[60vh] w-full rounded-xl bg-black object-contain"
                        controls preload="metadata" playsinline
                        <?php if($file->posterUrl()): ?> poster="<?php echo e($file->posterUrl()); ?>" <?php endif; ?>
                    >
                        <source src="<?php echo e($file->temporaryUrl()); ?>" type="<?php echo e($file->mime); ?>">
                        Il tuo browser non supporta la riproduzione video.
                    </video>
                <?php else: ?>
                    <img src="<?php echo e($file->temporaryUrl()); ?>" alt="<?php echo e($opportunita->description); ?>"
                         class="mx-auto max-h-[60vh] w-full rounded-xl object-contain bg-slate-50">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media->count() > 1): ?>
            <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Contenuti multimediali">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" @click="attivo = <?php echo e($indice); ?>" role="tab"
                            :aria-selected="attivo === <?php echo e($indice); ?>"
                            class="flex size-16 shrink-0 items-center justify-center rounded-lg border-2 bg-slate-100 text-xs font-semibold"
                            :class="attivo === <?php echo e($indice); ?> ? 'border-laguna-500' : 'border-transparent'">
                        <?php echo e($file->isVideo() ? '▶ Video' : 'Foto '.($indice + 1)); ?>

                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/galleria-media.blade.php ENDPATH**/ ?>