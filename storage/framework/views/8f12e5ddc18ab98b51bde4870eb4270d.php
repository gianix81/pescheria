<?php
    $bozze = $conteggi[\App\Enums\OpportunityStatus::BOZZA->value] ?? 0;
    $inVerifica = $conteggi[\App\Enums\OpportunityStatus::IN_VERIFICA->value] ?? 0;
    $daCorreggere = $conteggi[\App\Enums\OpportunityStatus::DA_CORREGGERE->value] ?? 0;
    // Aperte davvero: lo stato da solo conterebbe anche quelle già scadute.
    $aperte = $aperteReali;

    // Una sola frase, in ordine di urgenza: prima ciò che blocca.
    [$frase, $urgente] = match (true) {
        $daCorreggere > 0 => [$daCorreggere.' '.($daCorreggere === 1 ? 'opportunità respinta dal Tecnico: correggila e rimandala.' : 'opportunità respinte dal Tecnico: correggile e rimandale.'), true],
        $inScadenza->isNotEmpty() => [$inScadenza->count().' '.($inScadenza->count() === 1 ? 'opportunità scade' : 'opportunità scadono').' entro 6 ore: controlla chi manca.', true],
        $aperte > 0 => [$aperte.' '.($aperte === 1 ? 'opportunità aperta' : 'opportunità aperte').': stanno arrivando le risposte.', false],
        $bozze > 0 => [$bozze.' '.($bozze === 1 ? 'bozza da completare' : 'bozze da completare').' e inviare in verifica.', false],
        default => ['Nessuna opportunità in corso: pubblicane una nuova.', false],
    };
?>

<div class="space-y-4">

    <?php if (isset($component)) { $__componentOriginal8ad2c72140099ff785b8eb4be2bbd945 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.obiettivo','data' => ['titolo' => $frase,'urgente' => $urgente,'azione' => '＋ Nuova','urlAzione' => route('buyer.opportunita.create')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('obiettivo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($frase),'urgente' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($urgente),'azione' => '＋ Nuova','url-azione' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.opportunita.create'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945)): ?>
<?php $attributes = $__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945; ?>
<?php unset($__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ad2c72140099ff785b8eb4be2bbd945)): ?>
<?php $component = $__componentOriginal8ad2c72140099ff785b8eb4be2bbd945; ?>
<?php unset($__componentOriginal8ad2c72140099ff785b8eb4be2bbd945); ?>
<?php endif; ?>

    
    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        <?php if (isset($component)) { $__componentOriginal83671aaa862f9508064959943d48eebb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal83671aaa862f9508064959943d48eebb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Aperte','valore' => $aperte,'nota' => $daChiudere > 0 ? $daChiudere.' con termine passato' : null,'url' => route('opportunita.index', ['stato' => 'APERTA'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Aperte','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aperte),'nota' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daChiudere > 0 ? $daChiudere.' con termine passato' : null),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunita.index', ['stato' => 'APERTA']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $attributes = $__attributesOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__attributesOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $component = $__componentOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__componentOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal83671aaa862f9508064959943d48eebb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal83671aaa862f9508064959943d48eebb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'In verifica','valore' => $inVerifica,'url' => route('opportunita.index', ['stato' => 'IN_VERIFICA'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'In verifica','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inVerifica),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunita.index', ['stato' => 'IN_VERIFICA']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $attributes = $__attributesOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__attributesOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $component = $__componentOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__componentOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal83671aaa862f9508064959943d48eebb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal83671aaa862f9508064959943d48eebb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Da correggere','valore' => $daCorreggere,'evidenzia' => $daCorreggere > 0,'url' => route('opportunita.index', ['stato' => 'DA_CORREGGERE'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Da correggere','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daCorreggere),'evidenzia' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daCorreggere > 0),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunita.index', ['stato' => 'DA_CORREGGERE']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $attributes = $__attributesOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__attributesOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $component = $__componentOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__componentOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal83671aaa862f9508064959943d48eebb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal83671aaa862f9508064959943d48eebb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Bozze','valore' => $bozze,'url' => route('opportunita.index', ['stato' => 'BOZZA'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Bozze','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bozze),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunita.index', ['stato' => 'BOZZA']))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $attributes = $__attributesOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__attributesOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal83671aaa862f9508064959943d48eebb)): ?>
<?php $component = $__componentOriginal83671aaa862f9508064959943d48eebb; ?>
<?php unset($__componentOriginal83671aaa862f9508064959943d48eebb); ?>
<?php endif; ?>
    </div>

    
    <div class="card flex flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-3 text-sm">
        <span class="text-slate-600">Ordinato sulle aperte</span>
        <span class="flex flex-wrap items-center gap-x-5 gap-y-1">
            <span><strong class="text-lg text-slate-900"><?php echo e($colliTotali); ?></strong> colli</span>
            <span><strong class="text-lg text-slate-900"><?php echo e(\App\Support\Format::decimal($kgTotali, 0)); ?></strong> kg</span>
            <span><strong class="text-lg text-slate-900"><?php echo e(\App\Support\Format::percent($tassoRisposta, 0)); ?></strong> risposte</span>
        </span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($limitate->isNotEmpty()): ?>
        <section class="card p-4">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Disponibilità residue</h2>
            <ul class="mt-2 divide-y divide-slate-100 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $limitate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center justify-between gap-3 py-2">
                        <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="min-w-0 flex-1 truncate font-medium text-mare-700">
                            <?php echo e($o->description); ?>

                        </a>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'shrink-0 text-xs font-bold',
                            'text-rose-700' => $o->isSoldOut(),
                            'text-emerald-700' => ! $o->isSoldOut(),
                        ]); ?>">
                            <?php echo e($o->isSoldOut() ? 'Esaurito' : $o->remainingPackages().' colli'); ?>

                        </span>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="card overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Ultime opportunità</h2>
            <a href="<?php echo e(route('opportunita.index')); ?>" class="text-xs font-semibold text-laguna-600 underline">Tutte</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recenti->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vuoto','data' => ['titolo' => 'Nessuna opportunità','descrizione' => 'Crea la prima per i reparti pescheria.','icona' => '🐟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vuoto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => 'Nessuna opportunità','descrizione' => 'Crea la prima per i reparti pescheria.','icona' => '🐟']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <a href="<?php echo e(route('buyer.opportunita.create')); ?>" class="btn-primary">Nuova opportunità</a>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b)): ?>
<?php $attributes = $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b; ?>
<?php unset($__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe515dc4391a9afb6f51d25b08856b1b)): ?>
<?php $component = $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b; ?>
<?php unset($__componentOriginalfe515dc4391a9afb6f51d25b08856b1b); ?>
<?php endif; ?>
        <?php else: ?>
            
            <ul class="divide-y divide-slate-100 lg:hidden">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recenti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li>
                        <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="flex items-center justify-between gap-3 px-4 py-2.5">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900"><?php echo e($o->description); ?></span>
                                <span class="block truncate text-xs text-slate-500">
                                    <?php echo e($o->reference); ?> · consegna <?php echo e(\App\Support\Format::date($o->delivery_date)); ?>

                                </span>
                            </span>
                            <?php if (isset($component)) { $__componentOriginal01813800a119859f70f95db356df6b8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01813800a119859f70f95db356df6b8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-stato','data' => ['stato' => $o->status,'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-stato'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status),'class' => 'shrink-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01813800a119859f70f95db356df6b8e)): ?>
<?php $attributes = $__attributesOriginal01813800a119859f70f95db356df6b8e; ?>
<?php unset($__attributesOriginal01813800a119859f70f95db356df6b8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01813800a119859f70f95db356df6b8e)): ?>
<?php $component = $__componentOriginal01813800a119859f70f95db356df6b8e; ?>
<?php unset($__componentOriginal01813800a119859f70f95db356df6b8e); ?>
<?php endif; ?>
                        </a>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Riferimento</th>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Stato</th>
                            <th scope="col" class="th">Scadenza</th>
                            <th scope="col" class="th">Consegna</th>
                            <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recenti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="td font-semibold"><?php echo e($o->reference); ?></td>
                                <td class="td"><?php echo e($o->article_code); ?> — <?php echo e($o->description); ?></td>
                                <td class="td"><?php if (isset($component)) { $__componentOriginal01813800a119859f70f95db356df6b8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01813800a119859f70f95db356df6b8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-stato','data' => ['stato' => $o->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-stato'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal01813800a119859f70f95db356df6b8e)): ?>
<?php $attributes = $__attributesOriginal01813800a119859f70f95db356df6b8e; ?>
<?php unset($__attributesOriginal01813800a119859f70f95db356df6b8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal01813800a119859f70f95db356df6b8e)): ?>
<?php $component = $__componentOriginal01813800a119859f70f95db356df6b8e; ?>
<?php unset($__componentOriginal01813800a119859f70f95db356df6b8e); ?>
<?php endif; ?></td>
                                <td class="td"><?php echo e(\App\Support\Format::dateTime($o->closes_at)); ?></td>
                                <td class="td"><?php echo e(\App\Support\Format::date($o->delivery_date)); ?></td>
                                <td class="td text-right whitespace-nowrap">
                                    <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="font-semibold text-laguna-600 underline">Apri</a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->status->isModificabile()): ?>
                                        <a href="<?php echo e(route('buyer.opportunita.edit', $o)); ?>" class="ml-2 font-semibold text-laguna-600 underline">Modifica</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/dashboard.blade.php ENDPATH**/ ?>