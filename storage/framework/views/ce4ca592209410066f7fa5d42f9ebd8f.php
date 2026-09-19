<div class="grid gap-5 lg:grid-cols-3">

    
    <div class="space-y-4 lg:col-span-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isRipubblicazione()): ?>
            <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                <p class="font-semibold"><span aria-hidden="true">✏️</span> Ripubblicazione</p>
                <p class="mt-1">
                    Questa opportunità era già aperta ed è stata modificata dal Buyer: in questo momento i punti
                    vendita non la vedono. Ha già raccolto <?php echo e($opportunity->responses()->count()); ?> risposte, che
                    restano valide. Confermala per rimetterla in linea.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($opportunity->reference); ?></p>
                    <h2 class="text-xl font-bold text-slate-900"><?php echo e($opportunity->title); ?></h2>
                </div>
                <?php if (isset($component)) { $__componentOriginal01813800a119859f70f95db356df6b8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01813800a119859f70f95db356df6b8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-stato','data' => ['stato' => $opportunity->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-stato'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity->status)]); ?>
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

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <?php if (isset($component)) { $__componentOriginala1b9a94c3af9b2990e975bb7b1c96aff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala1b9a94c3af9b2990e975bb7b1c96aff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.galleria-media','data' => ['opportunita' => $opportunity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('galleria-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunita' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala1b9a94c3af9b2990e975bb7b1c96aff)): ?>
<?php $attributes = $__attributesOriginala1b9a94c3af9b2990e975bb7b1c96aff; ?>
<?php unset($__attributesOriginala1b9a94c3af9b2990e975bb7b1c96aff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala1b9a94c3af9b2990e975bb7b1c96aff)): ?>
<?php $component = $__componentOriginala1b9a94c3af9b2990e975bb7b1c96aff; ?>
<?php unset($__componentOriginala1b9a94c3af9b2990e975bb7b1c96aff); ?>
<?php endif; ?>
                <div class="space-y-4">
                    <?php if (isset($component)) { $__componentOriginal8fbd6c8eef0623bcd880aa942f609d41 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fbd6c8eef0623bcd880aa942f609d41 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prezzo-box','data' => ['opportunita' => $opportunity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('prezzo-box'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunita' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fbd6c8eef0623bcd880aa942f609d41)): ?>
<?php $attributes = $__attributesOriginal8fbd6c8eef0623bcd880aa942f609d41; ?>
<?php unset($__attributesOriginal8fbd6c8eef0623bcd880aa942f609d41); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fbd6c8eef0623bcd880aa942f609d41)): ?>
<?php $component = $__componentOriginal8fbd6c8eef0623bcd880aa942f609d41; ?>
<?php unset($__componentOriginal8fbd6c8eef0623bcd880aa942f609d41); ?>
<?php endif; ?>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-500">Codice</dt><dd class="font-semibold"><?php echo e($opportunity->article_code); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">PLU</dt><dd class="font-semibold"><?php echo e($opportunity->plu ?: '—'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::kg($opportunity->kg_per_package, 2)); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Disponibilità</dt><dd class="font-semibold"><?php echo e($opportunity->isLimited() ? $opportunity->total_packages.' colli' : 'Illimitata'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Apertura</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::dateTime($opportunity->opens_at)); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Scadenza</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::dateTime($opportunity->closes_at)); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::date($opportunity->delivery_date)); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Destinatari</dt><dd class="font-semibold"><?php echo e($opportunity->stores->count()); ?> PdV</dd></div>
                    </dl>
                    <p class="text-sm text-slate-600"><?php echo e($opportunity->commercial_description); ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <aside class="lg:sticky lg:top-20 lg:self-start">
        <div class="card p-5">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Checklist di verifica</h3>

            <ul class="mt-3 space-y-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    'anagrafica' => 'Codice articolo, PLU e descrizione corretti',
                    'prezzi' => 'Prezzi, IVA, ricarico e margine coerenti',
                    'confezionamento' => 'Kg per collo e lotto minimo verificati',
                    'tempi' => 'Scadenza e data di consegna sostenibili',
                    'media' => 'Foto o video rappresentativi del prodotto',
                    'destinatari' => 'Punti vendita destinatari corretti',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chiave => $etichetta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" wire:model="checklist.<?php echo e($chiave); ?>"
                                   class="mt-0.5 rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                            <span class="text-slate-700"><?php echo e($etichetta); ?></span>
                        </label>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problemi): ?>
                <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900" role="status">
                    <p class="font-semibold"><span aria-hidden="true">⚠</span> Blocchi alla pubblicazione</p>
                    <ul class="mt-1 list-inside list-disc">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $problemi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problema): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li><?php echo e($problema); ?></li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $opportunity->hasMedia() && ! $opportunity->media_exception): ?>
                        <button type="button" wire:click="$toggle('eccezioneMediaAperta')" class="mt-2 text-xs font-semibold underline">
                            Autorizza eccezione senza foto/video
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eccezioneMediaAperta): ?>
                <div class="mt-3 rounded-lg border border-slate-300 p-3">
                    <label for="motivazioneEccezioneMedia" class="label">Motivazione dell'eccezione *</label>
                    <textarea id="motivazioneEccezioneMedia" rows="2" wire:model="motivazioneEccezioneMedia" class="input py-2"></textarea>
                    <button type="button" wire:click="concediEccezioneMedia" class="btn-ghost mt-2 w-full">Registra eccezione</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('updatePrice', $opportunity)): ?>
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-800">Prezzo di vendita</p>
                    <p class="help">
                        Acquisto <?php echo e(\App\Support\Format::money($opportunity->purchase_price)); ?>/kg ·
                        IVA <?php echo e(\App\Support\Format::percent($opportunity->vat_rate, 0)); ?>

                    </p>

                    <div class="mt-2">
                        <label for="prezzoVendita" class="sr-only">Prezzo di vendita al pubblico</label>
                        <div class="flex items-center gap-2">
                            <input id="prezzoVendita" type="number" step="0.01" min="0.01"
                                   wire:model.live.debounce.400ms="prezzoVendita"
                                   class="input <?php $__errorArgs = ['prezzoVendita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <span class="shrink-0 text-sm text-slate-600">/kg</span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['prezzoVendita'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <dl class="mt-2 grid grid-cols-3 gap-2 text-center text-xs" aria-live="polite">
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500">Netto</dt>
                            <dd class="font-bold text-slate-900"><?php echo e(\App\Support\Format::money($this->prezziProposti['net'])); ?></dd>
                        </div>
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500" title="Utile diviso il prezzo di acquisto.">Ricarico</dt>
                            <dd class="font-bold text-slate-900"><?php echo e(\App\Support\Format::percent($this->prezziProposti['markup'])); ?></dd>
                        </div>
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500" title="Utile diviso il prezzo di vendita netto IVA.">Margine</dt>
                            <dd class="font-bold text-slate-900"><?php echo e(\App\Support\Format::percent($this->prezziProposti['margin'])); ?></dd>
                        </div>
                    </dl>

                    <div class="mt-2">
                        <label for="notaPrezzo" class="sr-only">Motivo della correzione</label>
                        <input id="notaPrezzo" wire:model="notaPrezzo" class="input py-2 text-sm"
                               placeholder="Motivo della correzione (facoltativo)">
                    </div>

                    <button type="button" wire:click="aggiornaPrezzo" class="btn-ghost mt-2 w-full"
                            <?php if((float) $prezzoVendita === (float) $opportunity->sale_price_gross): echo 'disabled'; endif; ?>>
                        Aggiorna prezzo
                    </button>

                    <p class="help">
                        Il Buyer viene avvisato e la correzione resta nell'audit log.
                        Dopo la pubblicazione il prezzo non è più modificabile da qui.
                    </p>
                </div>
            <?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['verifica'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error" role="alert"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-4">
                <label for="note" class="label">Note di verifica (facoltative)</label>
                <textarea id="note" rows="2" wire:model="note" class="input py-2"></textarea>
            </div>

            <div class="mt-4 space-y-2">
                <button type="button" wire:click="approva" class="btn-primary w-full">Approva e pubblica</button>
                <button type="button" wire:click="$toggle('rifiutoAperto')" class="btn-ghost w-full">Richiedi correzioni</button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rifiutoAperto): ?>
                <div class="mt-3 rounded-lg border border-rose-300 bg-rose-50 p-3">
                    <label for="motivazioneRifiuto" class="label">Motivazione del rifiuto *</label>
                    <textarea id="motivazioneRifiuto" rows="3" wire:model="motivazioneRifiuto" class="input py-2"></textarea>
                    <button type="button" wire:click="respingi" class="btn-danger mt-2 w-full">Rimanda al Buyer</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </aside>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/tecnico/verifica.blade.php ENDPATH**/ ?>