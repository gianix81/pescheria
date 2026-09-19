<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Accesso' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-mare-700 px-4 py-10 font-sans">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <span class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl bg-laguna-500 text-xl font-bold">P</span>
            <h1 class="text-xl font-bold">{{ config('app.name') }}</h1>
            <p class="text-sm text-mare-100">Opportunità di acquisto per i reparti pescheria</p>
        </div>

        <div class="card p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-mare-100">Accesso riservato al personale autorizzato.</p>

        <x-firma-progetto class="mt-3 text-center text-[11px] leading-relaxed text-mare-100/70" />
    </div>
</body>
</html>
