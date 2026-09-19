<div class="space-y-4">

    
    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filtro opportunità">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
            'da_completare' => ['Da rispondere', $conteggi['da_completare']],
            'bozze' => ['Bozze', $conteggi['bozze']],
            'inviate' => ['Inviate', $conteggi['inviate']],
            'storico' => ['Storico', null],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chiave => [$etichetta, $quanti]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button type="button" wire:click="aggiornaVista('<?php echo e($chiave); ?>')" role="tab"
                    aria-selected="<?php echo e($vista === $chiave ? 'true' : 'false'); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'btn gap-1.5 px-3 py-2 text-sm',
                        'bg-mare-700 text-white' => $vista === $chiave,
                        'border border-slate-300 bg-white text-slate-700' => $vista !== $chiave,
                    ]); ?>">
                <?php echo e($etichetta); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quanti): ?>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'rounded-full px-1.5 text-xs font-bold',
                        'bg-white/20 text-white' => $vista === $chiave,
                        'bg-slate-200 text-slate-700' => $vista !== $chiave,
                    ]); ?>"><?php echo e($quanti); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

        <div class="ml-auto flex flex-wrap gap-2">
            <label for="ricerca" class="sr-only">Cerca</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca"
                   placeholder="Cerca articolo o PLU" class="input w-48 py-2 text-sm sm:w-56">
            <label for="categoria" class="sr-only">Categoria</label>
            <select id="categoria" wire:model.live="categoria" class="input w-40 py-2 text-sm">
                <option value="">Tutte</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categorie; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($conteggi['da_completare'] > 0): ?>
        <?php if (isset($component)) { $__componentOriginal8ad2c72140099ff785b8eb4be2bbd945 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ad2c72140099ff785b8eb4be2bbd945 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.obiettivo','data' => ['titolo' => $conteggi['da_completare'].' '.($conteggi['da_completare'] === 1 ? 'opportunità aspetta la tua risposta' : 'opportunità aspettano la tua risposta').($prossimaScadenza ? ' — la prima scade il '.\App\Support\Format::dateTime($prossimaScadenza) : ''),'urgente' => true,'azione' => $vista === 'da_completare' ? null : 'Vedi','urlAzione' => $vista === 'da_completare' ? null : route('cr.opportunita.index', ['vista' => 'da_completare'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('obiettivo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conteggi['da_completare'].' '.($conteggi['da_completare'] === 1 ? 'opportunità aspetta la tua risposta' : 'opportunità aspettano la tua risposta').($prossimaScadenza ? ' — la prima scade il '.\App\Support\Format::dateTime($prossimaScadenza) : '')),'urgente' => true,'azione' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vista === 'da_completare' ? null : 'Vedi'),'url-azione' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vista === 'da_completare' ? null : route('cr.opportunita.index', ['vista' => 'da_completare']))]); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div wire:loading.delay class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 3; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="card space-y-3 p-4">
                <div class="skeleton h-32 w-full sm:h-40"></div>
                <div class="skeleton h-5 w-3/4"></div>
                <div class="skeleton h-4 w-1/2"></div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div wire:loading.remove.delay>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunita->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginalfe515dc4391a9afb6f51d25b08856b1b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe515dc4391a9afb6f51d25b08856b1b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vuoto','data' => ['titolo' => match ($vista) {
                    'da_completare' => 'Nessuna opportunità in attesa: sei in pari',
                    'bozze' => 'Nessuna bozza salvata',
                    'inviate' => 'Non hai ancora inviato risposte',
                    default => 'Nessuna opportunità nello storico',
                },'descrizione' => 'Quando il Buyer pubblica una nuova opportunità per il tuo punto vendita la trovi qui, con foto o video, prezzo e scadenza.','icona' => '🐟']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vuoto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['titolo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($vista) {
                    'da_completare' => 'Nessuna opportunità in attesa: sei in pari',
                    'bozze' => 'Nessuna bozza salvata',
                    'inviate' => 'Non hai ancora inviato risposte',
                    default => 'Nessuna opportunità nello storico',
                }),'descrizione' => 'Quando il Buyer pubblica una nuova opportunità per il tuo punto vendita la trovi qui, con foto o video, prezzo e scadenza.','icona' => '🐟']); ?>
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
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opportunita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $risposta = $risposte[$o->id] ?? null;
                        $anteprima = $o->media->first();
                        $totale = $ordinato[$o->id] ?? null;
                        $urgente = $o->status === \App\Enums\OpportunityStatus::APERTA
                            && $o->closes_at->isFuture()
                            && $o->closes_at->diffInHours(now(), true) <= 3;
                    ?>

                    <article class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'card flex flex-col overflow-hidden transition',
                        'ring-2 ring-amber-400' => $urgente && ! $risposta?->isSubmitted(),
                    ]); ?>">
                        
                        <a href="<?php echo e(route('cr.opportunita.show', $o)); ?>" class="block">
                            <div class="relative flex h-32 items-center justify-center bg-slate-100 sm:h-44">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anteprima && ! $anteprima->esiste()): ?>
                                    <span class="text-sm text-slate-500">Immagine non disponibile</span>
                                <?php elseif($anteprima && ! $anteprima->isVideo()): ?>
                                    <img src="<?php echo e($anteprima->temporaryUrl()); ?>" alt="" class="h-32 w-full object-cover sm:h-44">
                                <?php elseif($anteprima): ?>
                                    <span class="text-4xl" aria-hidden="true">▶</span>
                                    <span class="sr-only">Video disponibile</span>
                                <?php else: ?>
                                    <span class="text-sm text-slate-500">Nessuna immagine</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="absolute left-2 top-2 flex flex-wrap gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->status === \App\Enums\OpportunityStatus::APERTA && $o->closes_at->isFuture()): ?>
                                        <?php if (isset($component)) { $__componentOriginal56adb1b6deed215ed16a669b82b80bad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56adb1b6deed215ed16a669b82b80bad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.countdown','data' => ['scadenza' => $o->closes_at,'etichetta' => '','class' => 'shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('countdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['scadenza' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->closes_at),'etichetta' => '','class' => 'shadow-sm']); ?>
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
                                    <?php else: ?>
                                        <?php if (isset($component)) { $__componentOriginal01813800a119859f70f95db356df6b8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal01813800a119859f70f95db356df6b8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-stato','data' => ['stato' => $o->status,'class' => 'shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-stato'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status),'class' => 'shadow-sm']); ?>
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
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposta): ?>
                                    <div class="absolute right-2 top-2">
                                        <?php if (isset($component)) { $__componentOriginale533520368b906aed85b4eb8fa080c00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale533520368b906aed85b4eb8fa080c00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-risposta','data' => ['stato' => $risposta->status,'class' => 'shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-risposta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risposta->status),'class' => 'shadow-sm']); ?>
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
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->isSoldOut()): ?>
                                    <div class="absolute inset-x-0 bottom-0 bg-rose-700/90 px-2 py-1 text-center text-xs font-bold text-white">
                                        Esaurito
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-4">
                            <div>
                                <h3 class="text-base font-bold leading-tight text-slate-900">
                                    <a href="<?php echo e(route('cr.opportunita.show', $o)); ?>" class="hover:underline"><?php echo e($o->title); ?></a>
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    <?php echo e($o->article_code); ?> · PLU <?php echo e($o->plu ?: '—'); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->origin): ?> · <?php echo e($o->origin); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>

                            <dl class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-sm">
                                <div>
                                    <dt class="text-xs text-slate-500">Vendita</dt>
                                    <dd class="font-bold text-slate-900"><?php echo e(\App\Support\Format::money($o->sale_price_gross)); ?>/kg</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Peso collo</dt>
                                    <dd class="font-bold text-slate-900"><?php echo e(\App\Support\Format::kg($o->kg_per_package, 1)); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Disponibilità</dt>
                                    <dd class="font-semibold text-slate-800">
                                        <?php echo e($o->isLimited() ? $o->remainingPackages().' colli' : 'Illimitati'); ?>

                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Consegna</dt>
                                    <dd class="font-semibold text-slate-800"><?php echo e(\App\Support\Format::date($o->delivery_date)); ?></dd>
                                </div>
                            </dl>

                            <p class="rounded-lg bg-slate-50 px-2 py-1.5 text-xs text-slate-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totale && $totale->colli > 0): ?>
                                    <span aria-hidden="true">📦</span>
                                    Già ordinati <strong><?php echo e($totale->colli); ?> colli</strong>
                                    da <?php echo e($totale->punti_vendita); ?> <?php echo e($totale->punti_vendita === 1 ? 'punto vendita' : 'punti vendita'); ?>

                                <?php else: ?>
                                    <span aria-hidden="true">📦</span> Nessun ordine ancora: sei il primo
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risposta?->isSubmitted() && $risposta->packages > 0): ?>
                                <p class="text-sm font-semibold text-emerald-800">
                                    <span aria-hidden="true">✓</span>
                                    Hai ordinato <?php echo e($risposta->packages); ?> colli (<?php echo e(\App\Support\Format::kg($risposta->kg, 1)); ?>)
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <a href="<?php echo e(route('cr.opportunita.show', $o)); ?>"
                               class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-auto w-full', 'btn-primary' => ! $risposta?->isSubmitted(), 'btn-ghost' => $risposta?->isSubmitted()]); ?>">
                                <?php echo e($risposta?->isSubmitted() ? 'Vedi o modifica' : 'Rispondi'); ?>

                            </a>
                        </div>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <div class="mt-6"><?php echo e($opportunita->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/cr/opportunita.blade.php ENDPATH**/ ?>