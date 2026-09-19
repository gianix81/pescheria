<div class="space-y-5">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->status === \App\Enums\OpportunityStatus::ANNULLATA): ?>
        <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⚠</span> <strong>Annullata</strong> il <?php echo e(\App\Support\Format::dateTime($opportunity->cancelled_at)); ?> — <?php echo e($opportunity->cancel_reason); ?>

        </div>
    <?php elseif($opportunity->status === \App\Enums\OpportunityStatus::CHIUSA): ?>
        <div class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800" role="status">
            <span aria-hidden="true">■</span> <strong>Chiusa</strong> il <?php echo e(\App\Support\Format::dateTime($opportunity->closed_at)); ?> — <?php echo e($opportunity->close_reason); ?>

        </div>
    <?php elseif($opportunity->isSoldOut()): ?>
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <span aria-hidden="true">⊘</span> <strong>Esaurita:</strong> tutti i colli disponibili sono stati impegnati.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php $mediaMancanti = $opportunity->media->reject->esiste(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mediaMancanti->isNotEmpty()): ?>
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold">
                <span aria-hidden="true">🖼</span>
                <?php echo e($mediaMancanti->count()); ?>

                <?php echo e($mediaMancanti->count() === 1 ? 'file non si trova più sul disco' : 'file non si trovano più sul disco'); ?>

            </p>
            <p class="mt-1">
                Le righe ci sono ma il contenuto no: succede quando il disco non è persistente e viene
                azzerato a ogni pubblicazione.
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $opportunity)): ?>
                    <a href="<?php echo e(route('buyer.opportunita.edit', $opportunity)); ?>" class="font-semibold underline">Ricaricali dalla modifica</a>.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['azione'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error" role="alert"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($opportunity->reference); ?></p>
                <h2 class="text-xl font-bold text-slate-900"><?php echo e($opportunity->title); ?></h2>
                <p class="text-sm text-slate-600"><?php echo e($opportunity->article_code); ?> · PLU <?php echo e($opportunity->plu ?: '—'); ?> · <?php echo e($opportunity->description); ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->status === \App\Enums\OpportunityStatus::APERTA): ?>
                    <?php if (isset($component)) { $__componentOriginal56adb1b6deed215ed16a669b82b80bad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56adb1b6deed215ed16a669b82b80bad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.countdown','data' => ['scadenza' => $opportunity->closes_at]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('countdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['scadenza' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity->closes_at)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56adb1b6deed215ed16a669b82b80bad)): ?>
<?php $attributes = $__attributesOriginal56adb1b6deed215ed16a669b82b80bad; ?>
<?php unset($__attributesOriginal56adb1b6deed215ed16a669b82b80bad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56adb1b6deed215ed16a669b82b80bad)): ?>
<?php $component = $__componentOriginal56adb1b6deed215ed16a669b82b80bad; ?>
<?php unset($__componentOriginal56adb1b6deed215ed16a669b82b80bad); ?>
<?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isExpired()): ?>
                        <span class="badge bg-amber-50 text-amber-900 ring-amber-300">
                            <span aria-hidden="true">⚠</span> In attesa di chiusura automatica
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="mt-4 grid gap-5 lg:grid-cols-2">
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

                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::kg($opportunity->kg_per_package, 2)); ?></dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Lotto minimo</dt><dd class="font-semibold"><?php echo e($opportunity->min_lot); ?> (×<?php echo e($opportunity->order_multiple); ?>)</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Apertura</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::dateTime($opportunity->opens_at)); ?></dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Scadenza</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::dateTime($opportunity->closes_at)); ?></dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::date($opportunity->delivery_date)); ?></dd></div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Disponibilità</dt>
                        <dd class="font-semibold">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isLimited()): ?>
                                <?php echo e($opportunity->total_packages); ?> totali · <?php echo e($opportunity->committed_packages); ?> impegnati · <?php echo e($opportunity->remainingPackages()); ?> residui
                            <?php else: ?>
                                Colli illimitati
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dd>
                    </div>
                    <div><dt class="text-xs uppercase text-slate-500">Autore</dt><dd class="font-semibold"><?php echo e($opportunity->creator?->full_name); ?></dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Verificatore</dt><dd class="font-semibold"><?php echo e($opportunity->reviewer?->full_name ?? '—'); ?></dd></div>
                </dl>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->review_notes): ?>
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        <strong>Note del Tecnico:</strong> <?php echo e($opportunity->review_notes); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $opportunity)): ?>
                <a href="<?php echo e(route('buyer.opportunita.edit', $opportunity)); ?>" class="btn-ghost">Modifica</a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('duplicate', $opportunity)): ?>
                <button type="button" wire:click="duplica" class="btn-ghost">Duplica</button>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('review', $opportunity)): ?>
                <a href="<?php echo e(route('tecnico.verifica', $opportunity)); ?>" class="btn-primary">Verifica</a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('close', $opportunity)): ?>
                <button type="button" wire:click="apriAzione('chiudi')" class="btn-ghost">Chiudi</button>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cancel', $opportunity)): ?>
                <button type="button" wire:click="apriAzione('annulla')" class="btn-ghost text-rose-700">Annulla</button>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $opportunity)): ?>
                <button type="button" wire:click="apriAzione('elimina')" class="btn-danger">Elimina</button>
            <?php endif; ?>
            <button type="button" wire:click="sollecita" class="btn-secondary">Sollecita mancanti</button>
            <a href="<?php echo e(route('export.index', ['opportunity_id' => $opportunity->id])); ?>" class="btn-ghost">⤓ Export</a>
        </div>

        
        <?php $stato = $opportunity->status; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stato === \App\Enums\OpportunityStatus::IN_VERIFICA): ?>
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-800">
                    <?php echo e($opportunity->isRipubblicazione() ? 'Chiedi al Tecnico di ripubblicarla' : 'Avvisa i Tecnici su WhatsApp'); ?>

                </p>
                <p class="help">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isRipubblicazione()): ?>
                        L'opportunità è ferma: i punti vendita non la vedono finché un Tecnico non conferma.
                    <?php else: ?>
                        La notifica in-app è già partita: questo serve a farli intervenire subito.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>

                <div class="mt-3 space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tecnici; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-3 py-2">
                            <span class="text-sm">
                                <span class="font-semibold text-slate-900"><?php echo e($t->full_name); ?></span>
                                <span class="block text-xs text-slate-500"><?php echo e($t->phone ?: 'nessun numero in anagrafica'); ?></span>
                            </span>
                            <?php if (isset($component)) { $__componentOriginald8d79c01c01861173e30b7b1ad56c433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8d79c01c01861173e30b7b1ad56c433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.condividi-whatsapp','data' => ['testo' => $opportunity->isRipubblicazione()
                                    ? \App\Support\WhatsApp::perRipubblicazione($opportunity)
                                    : \App\Support\WhatsApp::perVerifica($opportunity),'numero' => $t->phone,'etichetta' => $t->phone ? 'Scrivi a '.$t->first_name : 'Scegli la chat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condividi-whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['testo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity->isRipubblicazione()
                                    ? \App\Support\WhatsApp::perRipubblicazione($opportunity)
                                    : \App\Support\WhatsApp::perVerifica($opportunity)),'numero' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t->phone),'etichetta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t->phone ? 'Scrivi a '.$t->first_name : 'Scegli la chat')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $attributes = $__attributesOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $component = $__componentOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__componentOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tecnici->isEmpty()): ?>
                        <p class="text-sm text-slate-600">Nessun Tecnico attivo in anagrafica.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php elseif($stato->isPubblicata() && $stato !== \App\Enums\OpportunityStatus::SCADUTA): ?>
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <?php $aggiornamento = $opportunity->reviews->count() > 1; ?>

                <p class="text-sm font-semibold text-emerald-900">
                    <?php echo e($aggiornamento ? 'Comunica l\'aggiornamento al gruppo dei reparti' : 'Annuncia l\'apertura nel gruppo dei reparti'); ?>

                </p>
                <?php if (isset($component)) { $__componentOriginald8d79c01c01861173e30b7b1ad56c433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8d79c01c01861173e30b7b1ad56c433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.condividi-whatsapp','data' => ['class' => 'mt-2','variante' => 'principale','testo' => $aggiornamento
                        ? \App\Support\WhatsApp::perAggiornamento($opportunity)
                        : \App\Support\WhatsApp::perApertura($opportunity),'etichetta' => $aggiornamento ? 'Comunica la modifica nel gruppo' : 'Condividi nel gruppo WhatsApp','descrizione' => 'Scegli il gruppo dei reparti pescheria e invia. Il messaggio porta il collegamento alla scheda: l\'ordine resta valido solo dall\'app.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condividi-whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-2','variante' => 'principale','testo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aggiornamento
                        ? \App\Support\WhatsApp::perAggiornamento($opportunity)
                        : \App\Support\WhatsApp::perApertura($opportunity)),'etichetta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($aggiornamento ? 'Comunica la modifica nel gruppo' : 'Condividi nel gruppo WhatsApp'),'descrizione' => 'Scegli il gruppo dei reparti pescheria e invia. Il messaggio porta il collegamento alla scheda: l\'ordine resta valido solo dall\'app.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $attributes = $__attributesOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $component = $__componentOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__componentOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stato->isPubblicata() && $mancanti->isNotEmpty()): ?>
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Sollecita chi non ha ancora risposto (<?php echo e($mancanti->count()); ?>)
                </p>
                <p class="help">Scrivi direttamente a chi ordina per quel punto vendita, senza passare dal gruppo.</p>

                <div class="mt-3 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $mancanti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-3 py-2">
                            <span class="text-sm">
                                <span class="font-semibold text-slate-900"><?php echo e($riga['store']->code); ?></span>
                                <span class="text-slate-600">— <?php echo e($riga['store']->name); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riga['utenti']->isEmpty()): ?>
                                    <span class="block text-xs text-rose-700">Nessun utente attivo</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $riga['utenti']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginald8d79c01c01861173e30b7b1ad56c433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8d79c01c01861173e30b7b1ad56c433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.condividi-whatsapp','data' => ['testo' => \App\Support\WhatsApp::perSollecito($opportunity),'numero' => $u->phone,'etichetta' => $u->phone ? 'Scrivi a '.$u->first_name : 'Scegli la chat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condividi-whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['testo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Support\WhatsApp::perSollecito($opportunity)),'numero' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($u->phone),'etichetta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($u->phone ? 'Scrivi a '.$u->first_name : 'Scegli la chat')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $attributes = $__attributesOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__attributesOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8d79c01c01861173e30b7b1ad56c433)): ?>
<?php $component = $__componentOriginald8d79c01c01861173e30b7b1ad56c433; ?>
<?php unset($__componentOriginald8d79c01c01861173e30b7b1ad56c433); ?>
<?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="grid gap-4 sm:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
            ['Destinatari', $statistiche['destinatari']],
            ['Risposte inviate', $statistiche['inviate']],
            ['Mancanti', $statistiche['mancanti']],
            ['Completamento', \App\Support\Format::percent($statistiche['percentuale'])],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$etichetta, $valore]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($etichetta); ?></p>
                <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo e($valore); ?></p>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Risposte dei punti vendita</h3>
            <p class="text-sm text-slate-700">
                Totale <strong><?php echo e($colliTotali); ?></strong> colli ·
                <strong><?php echo e(\App\Support\Format::decimal($kgTotali, 2)); ?></strong> kg
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Punto vendita</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th">Colli</th>
                        <th scope="col" class="th">Kg</th>
                        <th scope="col" class="th">Inviata</th>
                        <th scope="col" class="th">Utente</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunity->stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $r = $risposte[$store->id] ?? null; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="td font-medium"><?php echo e($store->code); ?> — <?php echo e($store->name); ?></td>
                            <td class="td"><?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $r?->status ?? $statoPredefinito]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r?->status ?? $statoPredefinito)]); ?>
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
                            <td class="td font-semibold"><?php echo e($r?->packages ?? 0); ?></td>
                            <td class="td"><?php echo e(\App\Support\Format::decimal($r?->kg ?? 0, 2)); ?></td>
                            <td class="td"><?php echo e($r?->submitted_at ? \App\Support\Format::dateTime($r->submitted_at) : '—'); ?></td>
                            <td class="td"><?php echo e($r?->lastActor?->full_name ?? '—'); ?></td>
                            <td class="td text-right">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r && auth()->user()->isTecnico()): ?>
                                    <button type="button" wire:click="apriAzione('riapri', <?php echo e($r->id); ?>)"
                                            class="font-semibold text-laguna-600 underline">Riapri</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r?->refusal_reason): ?>
                                    <span class="block text-xs text-slate-500"><?php echo e($r->refusal_reason); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($azione !== ''): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titolo-azione">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-azione" class="text-lg font-bold text-slate-900">
                    <?php echo e([
                        'chiudi' => 'Chiudi opportunità',
                        'annulla' => 'Annulla opportunità',
                        'riapri' => 'Riapri risposta',
                        'elimina' => 'Eliminare definitivamente?',
                    ][$azione]); ?>

                </h3>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($azione === 'elimina'): ?>
                    <div class="mt-2 space-y-2 text-sm text-slate-700">
                        <p>
                            <strong><?php echo e($opportunity->reference); ?></strong> — <?php echo e($opportunity->description); ?>

                        </p>
                        <p class="rounded-lg bg-rose-50 px-3 py-2 text-rose-900">
                            Spariscono anche <strong><?php echo e($opportunity->media->count()); ?></strong> file,
                            <strong><?php echo e($statistiche['inviate']); ?></strong> risposte dei punti vendita e
                            <strong><?php echo e($colliTotali); ?></strong> colli ordinati.
                            L'operazione non si annulla: nell'audit log resta la traccia di cosa è stato eliminato.
                        </p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->eliminabileSenzaMotivazione()): ?>
                            <p class="text-slate-600">
                                Scaduta da <?php echo e($opportunity->giorniDallaScadenza()); ?> giorni: la motivazione non è richiesta.
                            </p>
                        <?php else: ?>
                            <p class="text-slate-600">
                                Non è scaduta da almeno un mese: indica il motivo.
                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="mt-1 text-sm text-slate-600">La motivazione è obbligatoria e viene registrata nell'audit log.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($azione === 'riapri'): ?>
                    <div class="mt-3">
                        <label for="nuovaScadenza" class="label">Nuova scadenza per il punto vendita</label>
                        <input id="nuovaScadenza" type="datetime-local" wire:model="nuovaScadenza" class="input">
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($azione === 'elimina' && $opportunity->eliminabileSenzaMotivazione())): ?>
                    <div class="mt-3">
                        <label for="motivazione" class="label">Motivazione *</label>
                        <textarea id="motivazione" rows="3" wire:model="motivazione" class="input py-2"></textarea>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('azione', '')" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="conferma"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex-1', 'btn-danger' => $azione === 'elimina', 'btn-primary' => $azione !== 'elimina']); ?>">
                        <?php echo e($azione === 'elimina' ? 'Elimina definitivamente' : 'Conferma'); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/shared/opportunita-show.blade.php ENDPATH**/ ?>