<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div>
            <label for="stato" class="label">Stato risposta</label>
            <select id="stato" wire:model.live="stato" class="input py-2 text-sm">
                <option value="">Tutti</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stati; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($s->value); ?>"><?php echo e($s->label()); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div class="min-w-64">
            <label for="opportunita" class="label">Opportunità</label>
            <select id="opportunita" wire:model.live="opportunita" class="input py-2 text-sm">
                <option value="">Tutte</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunitaElenco; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($o->id); ?>"><?php echo e($o->reference); ?> — <?php echo e($o->description); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['correzione'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error" role="alert"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposte->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vuoto','data' => ['titolo' => 'Nessuna risposta','descrizione' => 'Le risposte compaiono qui appena i punti vendita iniziano a rispondere.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vuoto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => 'Nessuna risposta','descrizione' => 'Le risposte compaiono qui appena i punti vendita iniziano a rispondere.']); ?>
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
        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Opportunità</th>
                        <th scope="col" class="th">Punto vendita</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th">Colli</th>
                        <th scope="col" class="th">Kg</th>
                        <th scope="col" class="th">Inviata</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $risposte; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="td">
                                <a href="<?php echo e(route('opportunita.show', $r->opportunity)); ?>" class="font-semibold text-mare-700 underline">
                                    <?php echo e($r->opportunity->reference); ?>

                                </a>
                                <span class="block text-xs text-slate-500"><?php echo e($r->opportunity->description); ?></span>
                            </td>
                            <td class="td"><?php echo e($r->store->code); ?></td>
                            <td class="td"><?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $r->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status)]); ?>
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
<?php endif; ?></td>
                            <td class="td font-semibold"><?php echo e($r->packages); ?></td>
                            <td class="td"><?php echo e(\App\Support\Format::decimal($r->kg, 2)); ?></td>
                            <td class="td"><?php echo e($r->submitted_at ? \App\Support\Format::dateTime($r->submitted_at) : '—'); ?></td>
                            <td class="td text-right">
                                <button type="button" wire:click="apriCorrezione(<?php echo e($r->id); ?>)"
                                        class="font-semibold text-laguna-600 underline">Correggi</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div><?php echo e($risposte->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($correzioneId): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titolo-correzione">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-correzione" class="text-lg font-bold text-slate-900">Correzione eccezionale</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Modifica la quantità dopo la scadenza. L'intervento resta tracciato come correzione del Buyer.
                </p>

                <div class="mt-3">
                    <label for="correzioneColli" class="label">Colli</label>
                    <input id="correzioneColli" type="number" min="0" wire:model="correzioneColli" class="input">
                </div>

                <div class="mt-3">
                    <label for="correzioneMotivo" class="label">Motivazione *</label>
                    <textarea id="correzioneMotivo" rows="3" wire:model="correzioneMotivo" class="input py-2"></textarea>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('correzioneId', null)" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="salvaCorrezione" class="btn-primary flex-1">Salva correzione</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/ordini.blade.php ENDPATH**/ ?>