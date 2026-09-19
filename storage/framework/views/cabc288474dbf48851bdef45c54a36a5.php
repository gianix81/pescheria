<?php
    $daVerificareN = $daVerificare->count();
    $scadonoOggi = $inScadenzaOggi->count();

    // Il Tecnico è il ponte: prima ciò che tiene ferma un'opportunità, poi chi manca.
    [$frase, $urgente, $azione, $url] = match (true) {
        $daVerificareN > 0 => [
            $daVerificareN.' '.($daVerificareN === 1 ? 'opportunità aspetta la tua verifica' : 'opportunità aspettano la tua verifica').': finché non confermi, i reparti non la vedono.',
            true,
            'Verifica',
            route('tecnico.verifica', $daVerificare->first()),
        ],
        $mancantiTotali > 0 => [
            $mancantiTotali.' '.($mancantiTotali === 1 ? 'punto vendita non ha ancora risposto' : 'punti vendita non hanno ancora risposto').': sollecitali prima della scadenza.',
            $scadonoOggi > 0,
            'Monitor',
            route('tecnico.monitor'),
        ],
        default => ['Tutto in ordine: nessuna verifica in attesa e nessun punto vendita mancante.', false, 'Monitor', route('tecnico.monitor')],
    };
?>

<div class="space-y-4">

    <?php if (isset($component)) { $__componentOriginal8ad2c72140099ff785b8eb4be2bbd945 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.obiettivo','data' => ['titolo' => $frase,'urgente' => $urgente,'azione' => $azione,'urlAzione' => $url]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('obiettivo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($frase),'urgente' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($urgente),'azione' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($azione),'url-azione' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($url)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Da verificare','valore' => $daVerificareN,'evidenzia' => $daVerificareN > 0,'url' => route('opportunita.index', ['preset' => 'da_verificare'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Da verificare','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daVerificareN),'evidenzia' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daVerificareN > 0),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunita.index', ['preset' => 'da_verificare']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Scadono oggi','valore' => $scadonoOggi,'evidenzia' => $scadonoOggi > 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Scadono oggi','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scadonoOggi),'evidenzia' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scadonoOggi > 0)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'PdV mancanti','valore' => $mancantiTotali,'url' => route('tecnico.monitor')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'PdV mancanti','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mancantiTotali),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tecnico.monitor'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.riquadro-numero','data' => ['etichetta' => 'Anomalie','valore' => $anomalie->count()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('riquadro-numero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['etichetta' => 'Anomalie','valore' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($anomalie->count())]); ?>
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($daVerificare->isNotEmpty()): ?>
        <section class="card overflow-hidden">
            <h2 class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Da verificare</h2>
            <ul class="divide-y divide-slate-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daVerificare; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-slate-900"><?php echo e($o->title); ?></span>
                            <span class="block truncate text-xs text-slate-500">
                                <?php echo e($o->reference); ?> · <?php echo e($o->creator?->full_name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->isRipubblicazione()): ?> · <span class="text-amber-800">ripubblicazione</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </span>
                        <a href="<?php echo e(route('tecnico.verifica', $o)); ?>" class="btn-primary shrink-0 px-3 py-2 text-sm">Verifica</a>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Compilazioni</h2>
            <a href="<?php echo e(route('tecnico.monitor')); ?>" class="text-xs font-semibold text-laguna-600 underline">Matrice</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monitor->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vuoto','data' => ['titolo' => 'Nessuna opportunità aperta','icona' => '✓']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vuoto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => 'Nessuna opportunità aperta','icona' => '✓']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monitor; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $o = $riga['opportunita']; $s = $riga['stat']; ?>
                    <li class="px-4 py-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900"><?php echo e($o->description); ?></span>
                                <span class="block truncate text-xs text-slate-500">
                                    scade <?php echo e(\App\Support\Format::dateTime($o->closes_at)); ?>

                                </span>
                            </a>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'shrink-0 text-sm font-bold',
                                'text-rose-700' => $s['mancanti'] > 0,
                                'text-emerald-700' => $s['mancanti'] === 0,
                            ]); ?>"><?php echo e($s['inviate']); ?>/<?php echo e($s['destinatari']); ?></span>
                        </div>

                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded bg-slate-200">
                                <div class="h-full bg-laguna-500" style="width: <?php echo e($s['percentuale']); ?>%"></div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s['mancanti'] > 0): ?>
                                <button type="button" wire:click="sollecita(<?php echo e($o->id); ?>)"
                                        class="shrink-0 text-xs font-semibold text-laguna-600 underline">Sollecita</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Scadenza</th>
                            <th scope="col" class="th">Risposte</th>
                            <th scope="col" class="th">Mancanti</th>
                            <th scope="col" class="th">Completamento</th>
                            <th scope="col" class="th">Stato</th>
                            <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monitor; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $o = $riga['opportunita']; $s = $riga['stat']; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="td">
                                    <span class="block font-medium text-slate-900"><?php echo e($o->description); ?></span>
                                    <span class="block text-xs text-slate-500"><?php echo e($o->reference); ?></span>
                                </td>
                                <td class="td"><?php echo e(\App\Support\Format::dateTime($o->closes_at)); ?></td>
                                <td class="td"><?php echo e($s['inviate']); ?>/<?php echo e($s['destinatari']); ?></td>
                                <td class="td">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-semibold', 'text-rose-700' => $s['mancanti'] > 0]); ?>"><?php echo e($s['mancanti']); ?></span>
                                </td>
                                <td class="td">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded bg-slate-200">
                                            <div class="h-full bg-laguna-500" style="width: <?php echo e($s['percentuale']); ?>%"></div>
                                        </div>
                                        <span class="text-xs"><?php echo e(\App\Support\Format::percent($s['percentuale'])); ?></span>
                                    </div>
                                </td>
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
                                <td class="td text-right whitespace-nowrap">
                                    <button type="button" wire:click="sollecita(<?php echo e($o->id); ?>)" class="font-semibold text-laguna-600 underline">Sollecita</button>
                                    <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="ml-2 font-semibold text-laguna-600 underline">Dettaglio</a>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anomalie->isNotEmpty()): ?>
        <section class="card p-4">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Anomalie</h2>
            <ul class="mt-2 space-y-1.5 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $anomalie; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-900">
                        <span class="min-w-0 truncate">
                            <strong><?php echo e($riga['opportunita']->reference); ?></strong> —
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riga['stat']['bozze'] > 0): ?>
                                <?php echo e($riga['stat']['bozze']); ?> bozze non inviate
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riga['opportunita']->status === \App\Enums\OpportunityStatus::SCADUTA && $riga['stat']['mancanti'] > 0): ?>
                                <?php echo e($riga['stat']['mancanti']); ?> scaduti senza risposta
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                        <a href="<?php echo e(route('opportunita.show', $riga['opportunita'])); ?>" class="shrink-0 font-semibold underline">Apri</a>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/tecnico/dashboard.blade.php ENDPATH**/ ?>