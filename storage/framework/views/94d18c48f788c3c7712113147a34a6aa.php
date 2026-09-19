<div class="space-y-3">
    <div class="flex items-center justify-between">
        <p class="label">Punti vendita destinatari *</p>
        <div class="flex gap-2">
            <button type="button" class="text-xs font-semibold text-laguna-600 underline"
                    wire:click="$set('store_ids', <?php echo e(json_encode($puntiVendita->pluck('id'))); ?>)">Seleziona tutti</button>
            <button type="button" class="text-xs font-semibold text-laguna-600 underline"
                    wire:click="$set('store_ids', [])">Deseleziona</button>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $puntiVendita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-300 p-3 text-sm has-checked:border-mare-700 has-checked:bg-mare-50">
                <input type="checkbox" value="<?php echo e($pv->id); ?>" wire:model="store_ids"
                       class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                <span>
                    <span class="block font-semibold text-slate-900"><?php echo e($pv->code); ?></span>
                    <span class="block text-xs text-slate-600"><?php echo e($pv->name); ?> · <?php echo e($pv->city); ?></span>
                </span>
            </label>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['store_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/partials/destinatari.blade.php ENDPATH**/ ?>