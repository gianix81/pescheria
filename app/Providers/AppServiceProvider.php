<?php

namespace App\Providers;

use App\Policies\AnagraficaPolicy;
use App\Services\Availability\AllocationStrategy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->assicuraCartelleScrivibili();

        // La politica di assegnazione dello stock è sostituibile da configurazione (assunzione A4).
        $this->app->bind(AllocationStrategy::class, fn () => $this->app->make(config('pescheria.allocation_strategy')));
    }

    public function boot(): void
    {
        Carbon::setLocale('it');
        setlocale(LC_TIME, 'it_IT.UTF-8', 'it_IT', 'Italian');

        Gate::define('gestire-anagrafiche', [AnagraficaPolicy::class, 'manage']);
        Gate::define('gestire-account', [AnagraficaPolicy::class, 'manageAccounts']);

        $this->configureRateLimiting();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Ricrea le sottocartelle di storage/ se mancano.
     *
     * Serve quando un volume viene montato su /app/storage: il volume, vuoto,
     * copre la struttura creata durante il build e Laravel non trova più dove
     * compilare le viste. Sono quattro controlli su directory esistenti, senza
     * costo apprezzabile, e tolgono di mezzo un errore che a schermo si
     * presenterebbe come una pagina bianca.
     */
    private function assicuraCartelleScrivibili(): void
    {
        foreach ([
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ] as $cartella) {
            if (! is_dir($cartella)) {
                @mkdir($cartella, 0755, true);
            }
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);

        RateLimiter::for('upload', fn (Request $request) => Limit::perMinute(30)->by(optional($request->user())->id ?: $request->ip()));

        RateLimiter::for('risposte', fn (Request $request) => Limit::perMinute(40)->by(optional($request->user())->id ?: $request->ip()));

        RateLimiter::for('setup', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));

        RateLimiter::for('cron', fn (Request $request) => Limit::perMinute(12)->by($request->ip()));

        RateLimiter::for('export', fn (Request $request) => Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip()));
    }
}
