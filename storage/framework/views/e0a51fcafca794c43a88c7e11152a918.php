<div class="mx-auto max-w-6xl pb-32 lg:pb-8">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->status === \App\Enums\OpportunityStatus::ANNULLATA): ?>
        <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⚠</span> <strong>Opportunità annullata.</strong> <?php echo e($opportunity->cancel_reason); ?>

        </div>
    <?php elseif(! $apribile): ?>
        <div class="mb-4 rounded-lg border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800" role="status">
            <span aria-hidden="true">⏱</span>
            <strong>Risposte chiuse.</strong>
            Termine del <?php echo e(\App\Support\Format::dateTime($opportunity->closes_at)); ?>.
        </div>
    <?php elseif($opportunity->isSoldOut()): ?>
        <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⊘</span> <strong>Esaurito.</strong> Puoi ancora registrare il rifiuto.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <nav class="mb-3 text-sm text-slate-500" aria-label="Percorso">
        <a href="<?php echo e(route('cr.opportunita.index')); ?>" class="underline underline-offset-2">Opportunità</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-700"><?php echo e($opportunity->reference); ?></span>
    </nav>

    <div class="grid gap-6 lg:grid-cols-2">

        
        <div class="card p-4">
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
        </div>

        
        <div class="space-y-4">
            <div class="card p-5">
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($apribile): ?>
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
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposta): ?>
                        <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $risposta->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risposta->status)]); ?>
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
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <h2 class="mt-3 text-xl font-bold text-slate-900"><?php echo e($opportunity->title); ?></h2>
                <p class="mt-1 text-sm text-slate-600"><?php echo e($opportunity->commercial_description); ?></p>

                <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Codice articolo</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e($opportunity->article_code); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">PLU</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e($opportunity->plu ?: '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Kg per collo</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::kg($opportunity->kg_per_package, 2)); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Prezzo di vendita</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::money($opportunity->sale_price_gross)); ?>/kg</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Disponibilità</dt>
                        <dd class="font-semibold text-slate-900">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isLimited()): ?>
                                <?php echo e($residui); ?> colli residui su <?php echo e($opportunity->total_packages); ?>

                            <?php else: ?>
                                Colli illimitati
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Consegna</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e(\App\Support\Format::date($opportunity->delivery_date)); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Lotto minimo</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e($opportunity->min_lot); ?> colli (multipli di <?php echo e($opportunity->order_multiple); ?>)</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Origine</dt>
                        <dd class="font-semibold text-slate-900"><?php echo e($opportunity->origin ?: '—'); ?></dd>
                    </div>
                </dl>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->technical_notes || $opportunity->logistics_notes): ?>
                    <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-sm text-slate-600">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->technical_notes): ?><p><strong>Note tecniche:</strong> <?php echo e($opportunity->technical_notes); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->logistics_notes): ?><p><strong>Logistica:</strong> <?php echo e($opportunity->logistics_notes); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card border-2 border-mare-700/15 p-5" id="decisione">
                <h3 class="text-base font-bold text-slate-900">La tua decisione</h3>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ricevuta): ?>
                    <div class="mt-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        <span aria-hidden="true">✓</span> <?php echo e($ricevuta); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($apribile): ?>
                            <p class="mt-1 text-emerald-800">Puoi modificare la risposta fino alla scadenza.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($risposta?->isSubmitted()): ?>
                    <div class="mt-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        <span aria-hidden="true">✓</span>
                        <?php echo e($risposta->status->label()); ?> il <?php echo e(\App\Support\Format::dateTime($risposta->submitted_at)); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposta->packages > 0): ?>
                            — <?php echo e($risposta->packages); ?> colli (<?php echo e(\App\Support\Format::kg($risposta->kg, 2)); ?>)
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposta?->isSubmitted()): ?>
                    <?php if (isset($component)) { $__componentOriginald8d79c01c01861173e30b7b1ad56c433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8d79c01c01861173e30b7b1ad56c433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.condividi-whatsapp','data' => ['class' => 'mt-3','testo' => \App\Support\WhatsApp::perRisposta($risposta),'etichetta' => 'Comunica al gruppo','descrizione' => 'Il tuo ordine è già registrato: questo serve solo ad avvisare i colleghi nel gruppo.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('condividi-whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','testo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\App\Support\WhatsApp::perRisposta($risposta)),'etichetta' => 'Comunica al gruppo','descrizione' => 'Il tuo ordine è già registrato: questo serve solo ad avvisare i colleghi nel gruppo.']); ?>
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
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['invio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="error mt-3" role="alert"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($apribile): ?>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('decisione', 'acquista')"
                                <?php if($opportunity->isSoldOut()): echo 'disabled'; endif; ?>
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'btn',
                                    'bg-mare-700 text-white' => $decisione === 'acquista',
                                    'border border-slate-300 bg-white text-slate-700' => $decisione !== 'acquista',
                                ]); ?>"
                                aria-pressed="<?php echo e($decisione === 'acquista' ? 'true' : 'false'); ?>">
                            Acquista
                        </button>
                        <button type="button" wire:click="scegliColli(0)"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'btn',
                                    'bg-slate-800 text-white' => $decisione === 'non_acquista',
                                    'border border-slate-300 bg-white text-slate-700' => $decisione !== 'non_acquista',
                                ]); ?>"
                                aria-pressed="<?php echo e($decisione === 'non_acquista' ? 'true' : 'false'); ?>">
                            Non acquista
                        </button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['decisione'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decisione === 'acquista'): ?>
                        <fieldset class="mt-5">
                            <legend class="label">Quanti colli?</legend>

                            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-6">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunity->quickQuantities(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <button type="button" wire:click="scegliColli(<?php echo e($q); ?>)"
                                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'btn text-base',
                                                'bg-laguna-500 text-white' => (int) $colli === (int) $q,
                                                'border border-slate-300 bg-white text-slate-800' => (int) $colli !== (int) $q,
                                            ]); ?>"><?php echo e($q); ?></button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <button type="button" wire:click="decrementa" class="btn-ghost size-11 px-0" aria-label="Diminuisci colli">−</button>
                                <label for="colli" class="sr-only">Altra quantità in colli</label>
                                <input id="colli" type="number" inputmode="numeric" min="<?php echo e($opportunity->min_lot); ?>"
                                       step="<?php echo e($opportunity->order_multiple); ?>" wire:model.live="colli"
                                       class="input text-center text-lg font-semibold" placeholder="Altra quantità">
                                <button type="button" wire:click="incrementa" class="btn-ghost size-11 px-0" aria-label="Aumenta colli">+</button>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['colli'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <p class="mt-3 rounded-lg bg-mare-50 px-3 py-2 text-sm font-semibold text-mare-800" aria-live="polite">
                                <?php echo e((int) $colli); ?> colli × <?php echo e(\App\Support\Format::decimal($opportunity->kg_per_package, 2)); ?> kg
                                = <?php echo e(\App\Support\Format::decimal($kgPrevisti, 2)); ?> kg
                            </p>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isLimited()): ?>
                                <p class="help">Residui disponibili: <?php echo e($residui); ?> colli.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </fieldset>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decisione === 'non_acquista'): ?>
                        <div class="mt-5">
                            <label for="motivazione" class="label">
                                Motivazione
                                <?php echo e($opportunity->requires_refusal_reason || config('pescheria.require_refusal_reason') ? '(obbligatoria)' : '(facoltativa)'); ?>

                            </label>
                            <textarea id="motivazione" rows="2" wire:model="motivazione" class="input py-2"></textarea>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="mt-5 hidden gap-3 lg:flex">
                        <button type="button" wire:click="salvaBozza" class="btn-ghost flex-1">Salva bozza</button>
                        <button type="button" wire:click="apriConferma" class="btn-primary flex-1">Invia risposta</button>
                    </div>
                <?php else: ?>
                    <p class="mt-3 text-sm text-slate-600">
                        Le risposte sono chiuse. Per correzioni contatta il Tecnico.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-base font-bold text-slate-900">Ordini degli altri punti vendita</h3>
                    <p class="text-sm text-slate-600">
                        <strong class="text-slate-900"><?php echo e($colliTotali); ?></strong> colli
                        (<?php echo e(\App\Support\Format::decimal($kgTotali, 2)); ?> kg)
                        da <?php echo e($puntiVenditaConOrdine); ?> punti vendita
                    </p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->isLimited()): ?>
                    <?php $percentuale = $opportunity->total_packages > 0 ? min(100, round($colliTotali / $opportunity->total_packages * 100)) : 0; ?>
                    <div class="mt-3">
                        <div class="h-2 w-full overflow-hidden rounded bg-slate-200">
                            <div class="h-full <?php echo e($percentuale >= 90 ? 'bg-rose-600' : 'bg-laguna-500'); ?>" style="width: <?php echo e($percentuale); ?>%"></div>
                        </div>
                        <p class="help"><?php echo e($percentuale); ?>% della disponibilità già impegnato — restano <?php echo e($residui); ?> colli.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <ul class="mt-4 divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $classifica; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $riga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'flex items-center justify-between gap-3 py-2.5 text-sm',
                                'rounded-lg bg-mare-50 px-2' => $riga['proprio'],
                            ]); ?>">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="w-5 shrink-0 text-right text-xs font-semibold text-slate-400"><?php echo e($indice + 1); ?></span>
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-slate-900">
                                        <?php echo e($riga['store']->code); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riga['proprio']): ?>
                                            <span class="ml-1 rounded bg-mare-700 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">Tu</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <span class="block truncate text-xs text-slate-500"><?php echo e($riga['store']->name); ?></span>
                                </span>
                            </span>

                            <span class="flex shrink-0 items-center gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riga['colli'] > 0): ?>
                                    <span class="text-right">
                                        <span class="block font-bold text-slate-900"><?php echo e($riga['colli']); ?> colli</span>
                                        <span class="block text-xs text-slate-500"><?php echo e(\App\Support\Format::decimal($riga['kg'], 1)); ?> kg</span>
                                    </span>
                                <?php else: ?>
                                    <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $riga['stato']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riga['stato'])]); ?>
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
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>

                <p class="help mt-3">
                    Le quantità degli altri punti vendita sono visibili a tutti i destinatari.
                    Puoi modificare soltanto la tua risposta.
                </p>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($apribile): ?>
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white px-4 py-3 shadow-lg lg:hidden">
            <div class="mx-auto flex max-w-2xl items-center gap-3">
                <button type="button" wire:click="salvaBozza" class="btn-ghost flex-1">Bozza</button>
                <button type="button" wire:click="apriConferma" class="btn-primary flex-[2]">Invia risposta</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confermaAperta): ?>
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 sm:items-center"
             role="dialog" aria-modal="true" aria-labelledby="titolo-conferma">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-conferma" class="text-lg font-bold text-slate-900">Confermi la risposta?</h3>

                <div class="mt-3 space-y-1 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                    <p><strong><?php echo e($opportunity->description); ?></strong> (<?php echo e($opportunity->article_code); ?>)</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decisione === 'acquista'): ?>
                        <p><?php echo e((int) $colli); ?> colli × <?php echo e(\App\Support\Format::decimal($opportunity->kg_per_package, 2)); ?> kg
                            = <strong><?php echo e(\App\Support\Format::decimal($kgPrevisti, 2)); ?> kg</strong></p>
                    <?php else: ?>
                        <p><strong>Non acquisto</strong><?php echo e($motivazione ? ' — '.$motivazione : ''); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p>Consegna <?php echo e(\App\Support\Format::date($opportunity->delivery_date)); ?></p>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('confermaAperta', false)" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="invia" class="btn-primary flex-1" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="invia">Conferma e invia</span>
                        <span wire:loading wire:target="invia">Invio…</span>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/cr/scheda.blade.php ENDPATH**/ ?>