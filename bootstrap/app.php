<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\PreparaDatabaseDemo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Dietro il proxy di una piattaforma (Railway, Fly, load balancer) senza
        // questo Laravel genererebbe URL http e cookie non sicuri.
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            // Attivo solo in modalità dimostrativa: prepara il database usa e getta.
            PreparaDatabaseDemo::class,
        ]);

        $middleware->web(append: [
            EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

/*
| Su piattaforme serverless (Vercel) il filesystem del progetto è in sola
| lettura: storage/ viene spostato in /tmp tramite APP_STORAGE_PATH.
| In sviluppo e su un server tradizionale la variabile non è impostata e
| Laravel usa il percorso standard.
*/
if ($storagePath = env('APP_STORAGE_PATH')) {
    $app->useStoragePath($storagePath);
}

return $app;
