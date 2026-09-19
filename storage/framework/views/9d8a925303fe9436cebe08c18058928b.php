<div class="space-y-4">
    <fieldset>
        <legend class="label">Disponibilità</legend>
        <div class="mt-2 grid gap-3 sm:grid-cols-2">
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-300 p-3 has-checked:border-mare-700 has-checked:bg-mare-50">
                <input type="radio" wire:model.live="availability_type" value="APERTA" class="text-mare-700 focus:ring-laguna-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Colli illimitati</span>
                    <span class="block text-xs text-slate-600">Nessun limite globale</span>
                </span>
            </label>
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-300 p-3 has-checked:border-mare-700 has-checked:bg-mare-50">
                <input type="radio" wire:model.live="availability_type" value="LIMITATA" class="text-mare-700 focus:ring-laguna-500">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Colli limitati</span>
                    <span class="block text-xs text-slate-600">Primo che conferma, primo servito</span>
                </span>
            </label>
        </div>
    </fieldset>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availability_type === 'LIMITATA'): ?>
        <div>
            <label for="total_packages" class="label">Colli totali disponibili *</label>
            <input id="total_packages" type="number" min="1" wire:model="total_packages"
                   class="input <?php $__errorArgs = ['total_packages'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['total_packages'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="opens_at" class="label">Apertura ordini *</label>
            <input id="opens_at" type="datetime-local" wire:model="opens_at" class="input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['opens_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label for="closes_at" class="label">Scadenza ordini *</label>
            <input id="closes_at" type="datetime-local" wire:model="closes_at"
                   class="input <?php $__errorArgs = ['closes_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['closes_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label for="delivery_date" class="label">Consegna ai PdV *</label>
            <input id="delivery_date" type="date" wire:model="delivery_date" class="input">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['delivery_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <p class="help">Gli orari sono in ora italiana (<?php echo e(config('app.display_timezone')); ?>).</p>

    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" wire:model="requires_refusal_reason" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
        Richiedi la motivazione quando un punto vendita non acquista
    </label>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/partials/disponibilita.blade.php ENDPATH**/ ?>