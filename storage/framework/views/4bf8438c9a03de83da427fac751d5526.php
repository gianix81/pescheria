<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="purchase_price" class="label">Acquisto EUR/kg *</label>
            <input id="purchase_price" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="purchase_price"
                   class="input <?php $__errorArgs = ['purchase_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['purchase_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label for="sale_price_gross" class="label">Vendita EUR/kg (IVA inclusa) *</label>
            <input id="sale_price_gross" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="sale_price_gross"
                   class="input <?php $__errorArgs = ['sale_price_gross'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sale_price_gross'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label for="vat_rate" class="label">Aliquota IVA % *</label>
            <input id="vat_rate" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="vat_rate" class="input">
        </div>
    </div>

    
    <div class="grid gap-3 rounded-lg bg-mare-50 p-4 sm:grid-cols-3" aria-live="polite">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700">Vendita netto IVA</p>
            <p class="text-lg font-bold text-mare-800"><?php echo e(\App\Support\Format::money($prezzi['net'])); ?></p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700" title="Utile diviso il prezzo di acquisto. È il valore che oggi nel gruppo WhatsApp viene chiamato impropriamente margine.">
                Ricarico ⓘ
            </p>
            <p class="text-lg font-bold text-mare-800"><?php echo e(\App\Support\Format::percent($prezzi['markup'])); ?></p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700" title="Utile diviso il prezzo di vendita netto IVA. È il margine commerciale.">
                Margine ⓘ
            </p>
            <p class="text-lg font-bold text-mare-800"><?php echo e(\App\Support\Format::percent($prezzi['margin'])); ?></p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="kg_per_package" class="label">Kg per collo *</label>
            <input id="kg_per_package" type="number" step="0.001" min="0.001" wire:model="kg_per_package"
                   class="input <?php $__errorArgs = ['kg_per_package'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kg_per_package'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label for="min_lot" class="label">Lotto minimo (colli)</label>
            <input id="min_lot" type="number" min="1" wire:model="min_lot" class="input">
        </div>
        <div>
            <label for="order_multiple" class="label">Multiplo di ordinazione</label>
            <input id="order_multiple" type="number" min="1" wire:model="order_multiple" class="input">
        </div>
    </div>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/partials/prezzi.blade.php ENDPATH**/ ?>