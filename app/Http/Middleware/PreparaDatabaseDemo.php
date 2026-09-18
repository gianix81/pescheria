<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * In modalità dimostrativa il database è un file SQLite temporaneo: alla prima
 * richiesta di ogni istanza serverless non esiste ancora, quindi va creato,
 * migrato e popolato. Fuori dalla demo il middleware non fa nulla.
 */
class PreparaDatabaseDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('pescheria.demo.enabled') && ! $this->databasePronto()) {
            $this->inizializza();
        }

        return $next($request);
    }

    private function percorso(): string
    {
        return (string) config('pescheria.demo.database');
    }

    private function databasePronto(): bool
    {
        $percorso = $this->percorso();

        return is_file($percorso) && filesize($percorso) > 0;
    }

    private function inizializza(): void
    {
        $percorso = $this->percorso();
        $cartella = dirname($percorso);

        if (! is_dir($cartella)) {
            @mkdir($cartella, 0755, true);
        }

        // Lock grossolano: se due richieste arrivano insieme sulla stessa
        // istanza, la seconda aspetta invece di rimigrare sopra la prima.
        $lock = $percorso.'.lock';
        $maniglia = @fopen($lock, 'c');

        if ($maniglia && flock($maniglia, LOCK_EX)) {
            try {
                if (! $this->databasePronto()) {
                    touch($percorso);

                    Artisan::call('migrate', ['--force' => true, '--seed' => true]);

                    Log::info('Database dimostrativo inizializzato', ['percorso' => $percorso]);
                }
            } catch (\Throwable $e) {
                Log::error('Inizializzazione demo non riuscita: '.$e->getMessage());
            } finally {
                flock($maniglia, LOCK_UN);
                fclose($maniglia);
            }
        }
    }
}
