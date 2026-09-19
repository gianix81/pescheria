<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Accesso'); ?> · <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="flex min-h-full items-center justify-center bg-mare-700 px-4 py-10 font-sans">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <span class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl bg-laguna-500 text-xl font-bold">P</span>
            <h1 class="text-xl font-bold"><?php echo e(config('app.name')); ?></h1>
            <p class="text-sm text-mare-100">Opportunità di acquisto per i reparti pescheria</p>
        </div>

        <div class="card p-6 sm:p-8">
            <?php echo e($slot); ?>

        </div>

        <p class="mt-6 text-center text-xs text-mare-100">Accesso riservato al personale autorizzato.</p>

        <?php if (isset($component)) { $__componentOriginal74259611836b43e6a38c241312350104 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal74259611836b43e6a38c241312350104 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.firma-progetto','data' => ['class' => 'mt-3 text-center text-[11px] leading-relaxed text-mare-100/70']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('firma-progetto'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3 text-center text-[11px] leading-relaxed text-mare-100/70']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal74259611836b43e6a38c241312350104)): ?>
<?php $attributes = $__attributesOriginal74259611836b43e6a38c241312350104; ?>
<?php unset($__attributesOriginal74259611836b43e6a38c241312350104); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal74259611836b43e6a38c241312350104)): ?>
<?php $component = $__componentOriginal74259611836b43e6a38c241312350104; ?>
<?php unset($__componentOriginal74259611836b43e6a38c241312350104); ?>
<?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH /Users/direttore/Documents/pescheriasole/resources/views/components/layouts/auth.blade.php ENDPATH**/ ?>