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
        // La politica di assegnazione dello stock è sostituibile da configurazione (assunzione A4).
        $this->app->bind(AllocationStrategy::class, fn () => $this->app->make(config('pescheria.allocation_strategy')));
    }

    public function boot(): void
    {
        Carbon::setLocale('it');
        setlocale(LC_TIME, 'it_IT.UTF-8', 'it_IT', 'Italian');

        Gate::define('gestire-anagrafiche', [AnagraficaPolicy::class, 'manage']);

        $this->configureRateLimiting();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
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

        RateLimiter::for('export', fn (Request $request) => Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip()));
    }
}
