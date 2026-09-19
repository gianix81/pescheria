<?php
    $ok = fn (bool $condizione) => $condizione
        ? ['✓', 'text-emerald-800', 'bg-emerald-50 border-emerald-200']
        : ['⚠', 'text-rose-800', 'bg-rose-50 border-rose-200'];

    $schedulerAttivo = $scheduler !== null && $schedulerMinuti !== null && $schedulerMinuti <= 5;
    $mediaOk = $media['persistente'] === true;
?>

<div class="mx-auto max-w-3xl space-y-4">

    <p class="text-sm text-slate-600">
        Questa pagina dice in che stato si trova l'ambiente in questo momento. Quando qualcosa non
        torna, si guarda prima qui.
    </p>

    
    <section class="card p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Pubblicazione</h2>
        <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Versione in linea</dt>
                <dd class="font-mono font-semibold text-slate-900"><?php echo e($versione); ?></dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Ambiente</dt>
                <dd class="font-semibold text-slate-900"><?php echo e($ambiente); ?></dd>
            </div>
        </dl>
        <p class="help">Confronta la versione con l'ultimo commit del repository: se differiscono, la pubblicazione non è arrivata.</p>
    </section>

    
    <?php [$icona, $testo, $sfondo] = $ok($databaseOk && $migrazioni === 0 && ! $demo); ?>
    <section class="card border <?php echo e($sfondo); ?> p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Database</h2>
        <p class="mt-1 text-sm font-semibold <?php echo e($testo); ?>">
            <span aria-hidden="true"><?php echo e($icona); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($demo): ?>
                Modalità dimostrativa attiva: i dati sono temporanei
            <?php elseif(! $databaseOk): ?>
                Database non raggiungibile
            <?php elseif($migrazioni > 0): ?>
                <?php echo e($migrazioni); ?> migrazioni non applicate
            <?php else: ?>
                Collegato e allineato
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($migrazioni > 0): ?>
            <p class="help">Esegui <code class="rounded bg-white px-1">php artisan migrate --force</code> e imposta il pre-deploy step, altrimenti resterà indietro a ogni pubblicazione.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <dl class="mt-2 grid gap-1.5 text-sm sm:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $utenti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ruolo => $quanti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex justify-between gap-2">
                    <dt class="truncate text-slate-600"><?php echo e($ruolo); ?></dt>
                    <dd class="font-semibold text-slate-900"><?php echo e($quanti); ?></dd>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-600">Punti vendita</dt>
                <dd class="font-semibold text-slate-900"><?php echo e($puntiVendita); ?></dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-600">Opportunità</dt>
                <dd class="font-semibold text-slate-900"><?php echo e($opportunita); ?></dd>
            </div>
        </dl>
    </section>

    
    <?php [$icona, $testo, $sfondo] = $ok($mediaOk && $media['mancanti'] === 0); ?>
    <section class="card border <?php echo e($sfondo); ?> p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Foto e video</h2>
        <p class="mt-1 text-sm font-semibold <?php echo e($testo); ?>">
            <span aria-hidden="true"><?php echo e($mediaOk && $media['mancanti'] === 0 ? '✓' : ($media['persistente'] === null ? 'ⓘ' : '⚠')); ?></span>
            Disco persistente: <?php echo e($media['descrizione']); ?>

        </p>

        <dl class="mt-2 grid gap-1.5 text-sm sm:grid-cols-2">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">File registrati</dt>
                <dd class="font-semibold text-slate-900"><?php echo e($media['registrati']); ?></dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">File non trovati</dt>
                <dd class="font-semibold <?php echo e($media['mancanti'] > 0 ? 'text-rose-800' : 'text-slate-900'); ?>"><?php echo e($media['mancanti']); ?></dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-600">Percorso</dt>
                <dd class="break-all font-mono text-xs text-slate-700"><?php echo e($media['percorso']); ?></dd>
            </div>
        </dl>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media['persistente'] !== true): ?>
            <p class="help">
                Serve un volume sul servizio dell'applicazione. Se lo monti su un percorso dedicato,
                indicalo con <code class="rounded bg-white px-1">MEDIA_ROOT</code>; il percorso qui sopra
                deve corrispondere.
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    
    <?php [$icona, $testo, $sfondo] = $ok($schedulerAttivo); ?>
    <section class="card border <?php echo e($sfondo); ?> p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Automatismi</h2>
        <p class="mt-1 text-sm font-semibold <?php echo e($testo); ?>">
            <span aria-hidden="true"><?php echo e($icona); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($scheduler === null): ?>
                Lo scheduler non è mai stato eseguito
            <?php elseif($schedulerAttivo): ?>
                Attivo — ultima esecuzione <?php echo e($schedulerMinuti === 0 ? 'meno di un minuto fa' : $schedulerMinuti.' minuti fa'); ?>

            <?php else: ?>
                Fermo da <?php echo e($schedulerMinuti); ?> minuti (ultima: <?php echo e(\App\Support\Format::dateTime($scheduler)); ?>)
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($schedulerAttivo)): ?>
            <p class="help">
                Senza, le opportunità programmate non si aprono, quelle scadute restano aperte e non
                partono solleciti né riepiloghi. Serve un servizio con
                <code class="rounded bg-white px-1">php artisan schedule:work</code>, oppure un cron
                esterno che chiami <code class="rounded bg-white px-1">/cron/esegui</code> ogni minuto.
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/livewire/tecnico/stato-sistema.blade.php ENDPATH**/ ?>