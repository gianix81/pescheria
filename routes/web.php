<?php

use App\Http\Controllers\AperturaOpportunitaController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\StatoController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotte applicative
|--------------------------------------------------------------------------
| L'autorizzazione è sempre doppia: middleware di ruolo sul gruppo di rotte
| e policy sul singolo modello. Nascondere i pulsanti non basta.
*/

// Esecuzione dei comandi pianificati dove non c'è un cron di sistema.
// Protetto da CRON_SECRET; senza segreto valido risponde 404.
Route::get('/cron/esegui', [CronController::class, 'run'])
    ->middleware('throttle:cron')
    ->name('cron.run');

// Health check della piattaforma: risponde 200 e dichiara la versione pubblicata.
Route::get('/up', StatoController::class)->name('up');

// Primo accesso: crea il Super Admin iniziale dal browser. Esiste solo se
// SETUP_TOKEN è impostato e non c'è ancora un Super Admin attivo.
Route::middleware('throttle:setup')->group(function () {
    Route::get('/setup/{token}', [SetupController::class, 'show'])->name('setup.show');
    Route::post('/setup/{token}', [SetupController::class, 'store'])->name('setup.store');
});

// ------------------------------------------------------------------ ospiti
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/password/dimenticata', [PasswordResetController::class, 'showRequest'])->name('password.request');
    Route::post('/password/dimenticata', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:login')->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

// ------------------------------------------------------------------ autenticati
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/password/cambia', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/password/cambia', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Indirizzo unico da mettere nei messaggi: porta ciascuno alla propria vista.
    Route::get('/o/{opportunity}', AperturaOpportunitaController::class)->name('opportunita.apri');

    Route::get('/notifiche', Livewire\Shared\Notifiche::class)->name('notifiche.index');
    Route::get('/notifiche/{notification}/apri', [HomeController::class, 'openNotification'])->name('notifiche.open');

    // Media privati: URL firmato e temporaneo, comunque ricontrollato dalla policy.
    Route::get('/media/{media}', [MediaController::class, 'show'])->middleware('signed')->name('media.show');
    Route::get('/media/{media}/poster', [MediaController::class, 'poster'])->middleware('signed')->name('media.poster');

    // ---------------------------------------------------------- Buyer
    Route::middleware('role:BUYER,ADMIN')->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/dashboard', Livewire\Buyer\Dashboard::class)->name('dashboard');
        Route::get('/opportunita/nuova', Livewire\Buyer\OpportunitaForm::class)->name('opportunita.create');
        Route::get('/opportunita/{opportunity}/modifica', Livewire\Buyer\OpportunitaForm::class)->name('opportunita.edit');
        Route::get('/ordini', Livewire\Buyer\Ordini::class)->name('ordini');
    });

    // ---------------------------------------------------------- Tecnico
    Route::middleware('role:TECNICO,ADMIN')->prefix('tecnico')->name('tecnico.')->group(function () {
        Route::get('/dashboard', Livewire\Tecnico\Dashboard::class)->name('dashboard');
        Route::get('/verifica/{opportunity}', Livewire\Tecnico\Verifica::class)->name('verifica');
        Route::get('/monitor', Livewire\Tecnico\Monitor::class)->name('monitor');
        Route::get('/audit', Livewire\Tecnico\Audit::class)->name('audit');

        Route::prefix('anagrafiche')->name('anagrafiche.')->group(function () {
            Route::get('/utenti', Livewire\Tecnico\Anagrafiche\Utenti::class)->name('utenti');
            Route::get('/punti-vendita', Livewire\Tecnico\Anagrafiche\PuntiVendita::class)->name('punti-vendita');
            Route::get('/prodotti', Livewire\Tecnico\Anagrafiche\Prodotti::class)->name('prodotti');
        });
    });

    // ---------------------------------------------------------- Buyer + Tecnico
    Route::middleware('role:BUYER,TECNICO,ADMIN')->group(function () {
        Route::get('/opportunita', Livewire\Shared\OpportunitaIndex::class)->name('opportunita.index');
        Route::get('/opportunita/{opportunity}', Livewire\Shared\OpportunitaShow::class)->name('opportunita.show');
        Route::get('/storico', Livewire\Shared\OpportunitaIndex::class)->defaults('preset', 'storico')->name('storico');

        Route::get('/export', Livewire\Shared\Esporta::class)->name('export.index');
        Route::middleware('throttle:export')->group(function () {
            Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
            Route::get('/export/xlsx', [ExportController::class, 'xlsx'])->name('export.xlsx');
            Route::get('/export/portale', [ExportController::class, 'portale'])->name('export.portale');
        });
    });

    // ---------------------------------------------------------- Capo Reparto
    Route::middleware('role:CAPO_REPARTO')->prefix('cr')->name('cr.')->group(function () {
        // La pagina d'ingresso è l'elenco delle opportunità, non una dashboard.
        Route::get('/opportunita', Livewire\Cr\Opportunita::class)->name('opportunita.index');
        Route::redirect('/dashboard', '/cr/opportunita')->name('dashboard');
        Route::get('/opportunita/{opportunity}', Livewire\Cr\Scheda::class)->name('opportunita.show');
    });
});
