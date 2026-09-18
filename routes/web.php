<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotte applicative
|--------------------------------------------------------------------------
| L'autorizzazione è sempre doppia: middleware di ruolo sul gruppo di rotte
| e policy sul singolo modello. Nascondere i pulsanti non basta.
*/

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

    Route::get('/notifiche', Livewire\Shared\Notifiche::class)->name('notifiche.index');
    Route::get('/notifiche/{notification}/apri', [HomeController::class, 'openNotification'])->name('notifiche.open');

    // Media privati: URL firmato e temporaneo, comunque ricontrollato dalla policy.
    Route::get('/media/{media}', [MediaController::class, 'show'])->middleware('signed')->name('media.show');
    Route::get('/media/{media}/poster', [MediaController::class, 'poster'])->middleware('signed')->name('media.poster');

    // ---------------------------------------------------------- Buyer
    Route::middleware('role:BUYER')->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/dashboard', Livewire\Buyer\Dashboard::class)->name('dashboard');
        Route::get('/opportunita/nuova', Livewire\Buyer\OpportunitaForm::class)->name('opportunita.create');
        Route::get('/opportunita/{opportunity}/modifica', Livewire\Buyer\OpportunitaForm::class)->name('opportunita.edit');
        Route::get('/ordini', Livewire\Buyer\Ordini::class)->name('ordini');
    });

    // ---------------------------------------------------------- Tecnico
    Route::middleware('role:TECNICO')->prefix('tecnico')->name('tecnico.')->group(function () {
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
    Route::middleware('role:BUYER,TECNICO')->group(function () {
        Route::get('/opportunita', Livewire\Shared\OpportunitaIndex::class)->name('opportunita.index');
        Route::get('/opportunita/{opportunity}', Livewire\Shared\OpportunitaShow::class)->name('opportunita.show');
        Route::get('/storico', Livewire\Shared\OpportunitaIndex::class)->defaults('preset', 'storico')->name('storico');

        Route::get('/export', Livewire\Shared\Esporta::class)->name('export.index');
        Route::middleware('throttle:export')->group(function () {
            Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
            Route::get('/export/xlsx', [ExportController::class, 'xlsx'])->name('export.xlsx');
        });
    });

    // ---------------------------------------------------------- Capo Reparto
    Route::middleware('role:CAPO_REPARTO')->prefix('cr')->name('cr.')->group(function () {
        Route::get('/dashboard', Livewire\Cr\Dashboard::class)->name('dashboard');
        Route::get('/opportunita/{opportunity}', Livewire\Cr\Scheda::class)->name('opportunita.show');
    });
});
