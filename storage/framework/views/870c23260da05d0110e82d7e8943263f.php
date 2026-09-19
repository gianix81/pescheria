<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1">
            <label for="ricerca" class="label">Cerca opportunità</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>
        <div>
            <label for="stato" class="label">Stato</label>
            <select id="stato" wire:model.live="stato" class="input py-2 text-sm">
                <option value="">Tutti</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stati; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($s->value); ?>"><?php echo e($s->label()); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div>
            <label for="consegna" class="label">Consegna</label>
            <input id="consegna" type="date" wire:model.live="consegna" class="input py-2 text-sm">
        </div>
    </div>

    
    <div class="card flex flex-wrap gap-3 p-3 text-xs">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Enums\ResponseStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $s]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $attributes = $__attributesOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__attributesOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $component = $__componentOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__componentOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunita->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vuoto','data' => ['titolo' => 'Nessuna opportunità con questi filtri']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vuoto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => 'Nessuna opportunità con questi filtri']); ?>
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
        
        <div class="card hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th sticky left-0 z-10 bg-slate-50">Opportunità</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $puntiVendita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <th scope="col" class="th text-center" title="<?php echo e($pv->name); ?>"><?php echo e($pv->code); ?></th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <th scope="col" class="th text-center">Mancanti</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $righe = $risposte[$o->id] ?? collect();
                            $stat = $o->completionStats();
                            $destinatari = $o->stores->pluck('id')->all();
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="td sticky left-0 z-10 bg-white">
                                <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="font-semibold text-mare-700 underline"><?php echo e($o->reference); ?></a>
                                <span class="block text-xs text-slate-500"><?php echo e($o->description); ?></span>
                                <span class="block text-xs text-slate-500">Scade <?php echo e(\App\Support\Format::dateTime($o->closes_at)); ?></span>
                            </td>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $puntiVendita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <td class="td text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! in_array($pv->id, $destinatari, true)): ?>
                                        <span class="text-slate-300" aria-label="Non destinatario">·</span>
                                    <?php else: ?>
                                        <?php $r = $righe[$pv->id] ?? null; $stato = $r?->status ?? $statoPredefinito; ?>
                                        <button type="button" wire:click="toggleSelezione(<?php echo e($o->id); ?>, <?php echo e($pv->id); ?>)"
                                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'rounded px-1 py-0.5',
                                                    'ring-2 ring-laguna-500' => in_array($pv->id, $selezione[$o->id] ?? [], true),
                                                ]); ?>"
                                                title="<?php echo e($pv->code); ?> — <?php echo e($stato->label()); ?><?php echo e($r && $r->packages ? ' ('.$r->packages.' colli)' : ''); ?>">
                                            <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $stato,'compatto' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stato),'compatto' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $attributes = $__attributesOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__attributesOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $component = $__componentOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__componentOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r && $r->packages > 0): ?>
                                                <span class="block text-[10px] font-semibold text-slate-700"><?php echo e($r->packages); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <td class="td text-center font-semibold <?php echo e($stat['mancanti'] > 0 ? 'text-rose-700' : 'text-emerald-700'); ?>">
                                <?php echo e($stat['mancanti']); ?>

                            </td>
                            <td class="td whitespace-nowrap text-right">
                                <button type="button" wire:click="selezionaTuttiMancanti(<?php echo e($o->id); ?>)" class="text-xs font-semibold text-laguna-600 underline">
                                    Seleziona mancanti
                                </button>
                                <button type="button" wire:click="sollecitaSelezionati(<?php echo e($o->id); ?>)" class="ml-2 text-xs font-semibold text-laguna-600 underline">
                                    Sollecita
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="space-y-4 lg:hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $righe = $risposte[$o->id] ?? collect(); $stat = $o->completionStats(); ?>
                <section class="card p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="<?php echo e(route('opportunita.show', $o)); ?>" class="font-semibold text-mare-700 underline"><?php echo e($o->reference); ?></a>
                            <p class="text-xs text-slate-500"><?php echo e($o->description); ?></p>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal01813800a119859f70f95db356df6b8e = $component; } ?>
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
<?php endif; ?>
                    </div>

                    <p class="mt-2 text-xs text-slate-600">
                        <?php echo e($stat['inviate']); ?>/<?php echo e($stat['destinatari']); ?> risposte ·
                        <strong class="<?php echo e($stat['mancanti'] > 0 ? 'text-rose-700' : 'text-emerald-700'); ?>"><?php echo e($stat['mancanti']); ?> mancanti</strong>
                    </p>

                    <ul class="mt-3 divide-y divide-slate-100 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $o->stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $r = $righe[$pv->id] ?? null; $stato = $r?->status ?? $statoPredefinito; ?>
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span class="font-medium text-slate-800"><?php echo e($pv->code); ?></span>
                                <span class="flex items-center gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r && $r->packages > 0): ?><span class="text-xs text-slate-600"><?php echo e($r->packages); ?> colli</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $stato]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stato)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $attributes = $__attributesOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__attributesOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale533520368b906aed85b4eb8fa080c00)): ?>
<?php $component = $__componentOriginale533520368b906aed85b4eb8fa080c00; ?>
<?php unset($__componentOriginale533520368b906aed85b4eb8fa080c00); ?>
<?php endif; ?>
                                </span>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>

                    <button type="button" wire:click="sollecitaSelezionati(<?php echo e($o->id); ?>)" class="btn-secondary mt-3 w-full">
                        Sollecita mancanti
                    </button>
                </section>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/tecnico/monitor.blade.php ENDPATH**/ ?>