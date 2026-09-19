<div class="mx-auto max-w-4xl space-y-5">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert" tabindex="-1">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Correggi questi punti prima di continuare</p>
            <ul class="mt-1 list-inside list-disc">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $errore): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($errore); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->pubblicata): ?>
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Opportunità già pubblicata (<?php echo e($opportunity->status->label()); ?>)</p>
            <p class="mt-1">
                Salvando, l'opportunità <strong>torna in verifica</strong>: resta ferma finché un Tecnico non
                la conferma, e solo allora torna disponibile per i punti vendita. Le risposte già raccolte
                restano dove sono.
            </p>
            <p class="mt-1">
                Non puoi ridurre i colli sotto quelli già confermati, né togliere punti vendita che hanno
                già risposto. Ogni modifica finisce nell'audit log.
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['salvataggio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="error" role="alert"><span aria-hidden="true">⚠</span><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                <?php echo e($opportunity ? 'Modifica '.$opportunity->reference : 'Nuova opportunità'); ?>

            </h2>
            <p class="text-sm text-slate-600">Le due modalità salvano esattamente gli stessi dati.</p>
        </div>

        <div class="flex gap-2" role="tablist" aria-label="Modalità di creazione">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['rapida' => 'Creazione rapida', 'guidata' => 'Creazione guidata']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chiave => $etichetta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button type="button" wire:click="$set('modalita', '<?php echo e($chiave); ?>')" role="tab"
                        aria-selected="<?php echo e($modalita === $chiave ? 'true' : 'false'); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'btn px-3 py-2 text-sm',
                            'bg-mare-700 text-white' => $modalita === $chiave,
                            'border border-slate-300 bg-white text-slate-700' => $modalita !== $chiave,
                        ]); ?>"><?php echo e($etichetta); ?></button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalita === 'rapida'): ?>
        <div class="space-y-4">
            <section class="card p-5" aria-labelledby="sez-media">
                <h3 id="sez-media" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">1 · Video o foto</h3>
                <?php echo $__env->make('livewire.buyer.partials.media', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>

            <section class="card p-5" aria-labelledby="sez-articolo">
                <h3 id="sez-articolo" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">2 · Articolo</h3>
                <?php echo $__env->make('livewire.buyer.partials.articolo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>

            <section class="card p-5" aria-labelledby="sez-prezzi">
                <h3 id="sez-prezzi" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">3 · Prezzi e confezionamento</h3>
                <?php echo $__env->make('livewire.buyer.partials.prezzi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>

            <section class="card p-5" aria-labelledby="sez-disp">
                <h3 id="sez-disp" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">4 · Disponibilità, scadenza e consegna</h3>
                <?php echo $__env->make('livewire.buyer.partials.disponibilita', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>

            <section class="card p-5" aria-labelledby="sez-dest">
                <h3 id="sez-dest" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">5 · Destinatari</h3>
                <?php echo $__env->make('livewire.buyer.partials.destinatari', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </section>
        </div>
    <?php else: ?>
        
        <ol class="flex flex-wrap gap-2 text-sm" aria-label="Passaggi">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [1 => 'Prodotto', 2 => 'Media e prezzi', 3 => 'Disponibilità', 4 => 'Destinatari']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $numero => $etichetta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li>
                    <button type="button" wire:click="vaiAlPasso(<?php echo e($numero); ?>)"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'btn px-3 py-2',
                                'bg-mare-700 text-white' => $passo === $numero,
                                'border border-slate-300 bg-white text-slate-700' => $passo !== $numero,
                            ]); ?>"
                            aria-current="<?php echo e($passo === $numero ? 'step' : 'false'); ?>">
                        <?php echo e($numero); ?>. <?php echo e($etichetta); ?>

                    </button>
                </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ol>

        <div class="card p-5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($passo === 1): ?>
                <?php echo $__env->make('livewire.buyer.partials.articolo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="technical_notes" class="label">Note tecniche</label>
                        <textarea id="technical_notes" rows="2" wire:model="technical_notes" class="input py-2"></textarea>
                    </div>
                    <div>
                        <label for="logistics_notes" class="label">Note logistiche</label>
                        <textarea id="logistics_notes" rows="2" wire:model="logistics_notes" class="input py-2"></textarea>
                    </div>
                    <div>
                        <label for="origin" class="label">Origine</label>
                        <input id="origin" wire:model="origin" class="input">
                    </div>
                    <div>
                        <label for="fao_zone" class="label">Zona FAO</label>
                        <input id="fao_zone" wire:model="fao_zone" class="input">
                    </div>
                    <div>
                        <label for="production_method" class="label">Metodo di produzione/pesca</label>
                        <input id="production_method" wire:model="production_method" class="input">
                    </div>
                    <div>
                        <label for="caliber" class="label">Calibro</label>
                        <input id="caliber" wire:model="caliber" class="input">
                    </div>
                </div>
            <?php elseif($passo === 2): ?>
                <?php echo $__env->make('livewire.buyer.partials.media', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <hr class="my-5 border-slate-200">
                <?php echo $__env->make('livewire.buyer.partials.prezzi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($passo === 3): ?>
                <?php echo $__env->make('livewire.buyer.partials.disponibilita', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <?php echo $__env->make('livewire.buyer.partials.destinatari', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <hr class="my-5 border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Riepilogo</h3>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-slate-500">Articolo</dt><dd class="font-semibold"><?php echo e($article_code); ?> — <?php echo e($description); ?></dd></div>
                    <div><dt class="text-slate-500">Kg per collo</dt><dd class="font-semibold"><?php echo e($kg_per_package ?: '—'); ?></dd></div>
                    <div><dt class="text-slate-500">Disponibilità</dt><dd class="font-semibold"><?php echo e($availability_type === 'LIMITATA' ? $total_packages.' colli' : 'Illimitata'); ?></dd></div>
                    <div><dt class="text-slate-500">Scadenza</dt><dd class="font-semibold"><?php echo e($closes_at); ?></dd></div>
                    <div><dt class="text-slate-500">Consegna</dt><dd class="font-semibold"><?php echo e($delivery_date); ?></dd></div>
                    <div><dt class="text-slate-500">Destinatari</dt><dd class="font-semibold"><?php echo e(count($store_ids)); ?> punti vendita</dd></div>
                </dl>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex justify-between">
            <button type="button" wire:click="vaiAlPasso(<?php echo e($passo - 1); ?>)" <?php if($passo === 1): echo 'disabled'; endif; ?> class="btn-ghost">Indietro</button>
            <button type="button" wire:click="vaiAlPasso(<?php echo e($passo + 1); ?>)" <?php if($passo === 4): echo 'disabled'; endif; ?> class="btn-ghost">Avanti</button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problemiPubblicazione): ?>
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Prima dell'invio in verifica</p>
            <ul class="mt-1 list-inside list-disc">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $problemiPubblicazione; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problema): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($problema); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="sticky bottom-0 -mx-4 flex flex-wrap gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:mx-0 sm:rounded-xl sm:border sm:px-5">
        <button type="button" wire:click="salvaBozza"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex-1 sm:flex-none', 'btn-primary' => $this->pubblicata, 'btn-ghost' => ! $this->pubblicata]); ?>">
            <span wire:loading.remove wire:target="salvaBozza"><?php echo e($this->pubblicata ? 'Salva e ripubblica' : 'Salva bozza'); ?></span>
            <span wire:loading wire:target="salvaBozza">Salvataggio…</span>
        </button>

        <button type="button" wire:click="$toggle('anteprimaAperta')" class="btn-ghost flex-1 sm:flex-none">
            <?php echo e($anteprimaAperta ? 'Chiudi anteprima' : 'Anteprima CR'); ?>

        </button>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($this->pubblicata)): ?>
            <button type="button" wire:click="inviaInVerifica" class="btn-primary flex-1 sm:ml-auto sm:flex-none">
                <span wire:loading.remove wire:target="inviaInVerifica">Invia in verifica</span>
                <span wire:loading wire:target="inviaInVerifica">Invio…</span>
            </button>
        <?php else: ?>
            <a href="<?php echo e(route('opportunita.show', $opportunity)); ?>" class="btn-ghost flex-1 sm:ml-auto sm:flex-none">
                Torna alla scheda
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($anteprimaAperta): ?>
        <section class="card p-5" aria-label="Anteprima della scheda come la vedrà il punto vendita">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Anteprima vista punto vendita</h3>
            <div class="mt-4 grid gap-5 lg:grid-cols-2">
                <div class="rounded-xl bg-slate-100 p-8 text-center text-sm text-slate-500">
                    <?php echo e($opportunity?->media?->count() ? $opportunity->media->count().' contenuti multimediali' : 'Nessun media caricato'); ?>

                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900"><?php echo e($title ?: 'Titolo non impostato'); ?></h4>
                    <p class="mt-1 text-sm text-slate-600"><?php echo e($commercial_description); ?></p>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-500">Codice</dt><dd class="font-semibold"><?php echo e($article_code ?: '—'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">PLU</dt><dd class="font-semibold"><?php echo e($plu ?: '—'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold"><?php echo e($kg_per_package ?: '—'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Vendita</dt><dd class="font-semibold"><?php echo e(\App\Support\Format::money($sale_price_gross)); ?>/kg</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Disponibilità</dt><dd class="font-semibold"><?php echo e($availability_type === 'LIMITATA' ? $total_packages.' colli' : 'Colli illimitati'); ?></dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold"><?php echo e($delivery_date); ?></dd></div>
                    </dl>
                    <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = config('pescheria.quick_quantities'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span class="btn border border-slate-300 bg-white text-slate-700"><?php echo e($q); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/buyer/opportunita-form.blade.php ENDPATH**/ ?>