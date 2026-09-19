<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="card mb-4 p-4">
            <label for="ricerca" class="label">Cerca punto vendita</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Codice</th>
                        <th scope="col" class="th">Portale</th>
                        <th scope="col" class="th">Nome</th>
                        <th scope="col" class="th">Città</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $elenco; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="td font-semibold"><?php echo e($pv->code); ?></td>
                            <td class="td">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pv->portal_code): ?>
                                    <?php echo e($pv->portal_code); ?>

                                <?php else: ?>
                                    <span class="text-xs text-slate-400">= <?php echo e($pv->code); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td"><?php echo e($pv->name); ?></td>
                            <td class="td"><?php echo e($pv->city); ?> (<?php echo e($pv->province); ?>)</td>
                            <td class="td">
                                <span class="badge <?php echo e($pv->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-300' : 'bg-slate-200 text-slate-700 ring-slate-400'); ?>">
                                    <?php echo e($pv->is_active ? 'Attivo' : 'Disattivo'); ?>

                                </span>
                            </td>
                            <td class="td text-right">
                                <button type="button" wire:click="modifica(<?php echo e($pv->id); ?>)" class="font-semibold text-laguna-600 underline">Modifica</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($elenco->links()); ?></div>
    </div>

    <aside class="card h-fit p-5 lg:sticky lg:top-20">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                <?php echo e($modificaId ? 'Modifica punto vendita' : 'Nuovo punto vendita'); ?>

            </h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modificaId): ?>
                <button type="button" wire:click="nuovo" class="text-xs font-semibold text-laguna-600 underline">Nuovo</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="mt-4 space-y-3">
            <div>
                <label for="code" class="label">Codice *</label>
                <input id="code" wire:model="form.code" class="input <?php $__errorArgs = ['form.code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label for="portal_code" class="label">Codice cliente portale (solo se diverso)</label>
                <input id="portal_code" wire:model="form.portal_code" class="input" inputmode="numeric">
                <p class="help">
                    Da lasciare vuoto: nell'export il codice qui sopra vale già come codice cliente.
                    Compilalo solo se il portale usasse un numero diverso.
                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.portal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label for="name" class="label">Ragione sociale / nome *</label>
                <input id="name" wire:model="form.name" class="input <?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label for="address" class="label">Indirizzo</label>
                <input id="address" wire:model="form.address" class="input">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-2">
                    <label for="city" class="label">Città</label>
                    <input id="city" wire:model="form.city" class="input">
                </div>
                <div>
                    <label for="province" class="label">Prov.</label>
                    <input id="province" maxlength="2" wire:model="form.province" class="input">
                </div>
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" wire:model="form.email" class="input">
            </div>
            <div>
                <label for="phone" class="label">Telefono</label>
                <input id="phone" wire:model="form.phone" class="input">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="form.is_active" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                Attivo
            </label>

            <button type="button" wire:click="salva" class="btn-primary w-full">Salva</button>
        </div>
    </aside>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/tecnico/anagrafiche/punti-vendita.blade.php ENDPATH**/ ?>