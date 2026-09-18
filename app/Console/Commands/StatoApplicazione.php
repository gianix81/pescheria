<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Opportunity;
use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fotografia dell'ambiente, pensata per capire in trenta secondi perché
 * "le credenziali non funzionano": database raggiungibile? migrazioni
 * applicate? utenti presenti? profilo attivo? modalità dimostrativa accesa?
 *
 *   php artisan pescheria:stato
 */
class StatoApplicazione extends Command
{
    protected $signature = 'pescheria:stato
        {--email= : Verifica un account specifico}
        {--password= : Prova la password di quell\'account e dice quale controllo fallisce}';

    protected $description = 'Diagnostica ambiente, database e profili di accesso';

    public function handle(): int
    {
        $problemi = [];

        $this->components->info('Ambiente');
        $this->riga('APP_ENV', config('app.env'));
        $this->riga('APP_URL', config('app.url'));
        $this->riga('APP_KEY', config('app.key') ? 'impostata' : 'MANCANTE');
        $this->riga('Modalità demo', config('pescheria.demo.enabled') ? 'ATTIVA (dati temporanei)' : 'disattiva');
        $this->riga('Sessioni', config('session.driver'));

        if (! config('app.key')) {
            $problemi[] = 'APP_KEY non impostata: il login non può funzionare.';
        }

        if (config('pescheria.demo.enabled')) {
            $problemi[] = 'Modalità dimostrativa attiva: i dati si azzerano e non sono condivisi fra istanze.';
        }

        $this->newLine();
        $this->components->info('Database');
        $connessione = config('database.default');
        $this->riga('Connessione', $connessione);
        $this->riga('Database', config("database.connections.{$connessione}.database"));
        $this->riga('Host', (string) config("database.connections.{$connessione}.host"));

        try {
            DB::connection()->getPdo();
            $this->riga('Raggiungibile', 'sì');
        } catch (\Throwable $e) {
            $this->riga('Raggiungibile', 'NO — '.$e->getMessage());

            $this->newLine();
            $this->error('Senza database non è possibile autenticarsi. Verifica le variabili DB_*.');

            return self::FAILURE;
        }

        $mancanti = collect($this->laravel->make('migrator')->getMigrationFiles(database_path('migrations')))
            ->keys()
            ->diff($this->laravel->make('migrator')->getRepository()->getRan())
            ->count();

        $this->riga('Migrazioni non applicate', (string) $mancanti);

        if ($mancanti > 0) {
            $problemi[] = "Ci sono {$mancanti} migrazioni non applicate: esegui «php artisan migrate --force».";
        }

        $this->newLine();
        $this->components->info('Profili');

        $totale = User::count();
        $this->riga('Utenti totali', (string) $totale);

        if ($totale === 0) {
            $problemi[] = 'Nessun utente nel database: esegui «php artisan db:seed --force» oppure «php artisan pescheria:admin».';
        }

        $righe = [];

        foreach (Role::cases() as $ruolo) {
            $righe[] = [
                $ruolo->label(),
                User::where('role', $ruolo)->count(),
                User::where('role', $ruolo)->where('is_active', true)->count(),
            ];
        }

        $this->table(['Ruolo', 'Totali', 'Attivi'], $righe);

        if (User::where('role', Role::ADMIN)->where('is_active', true)->doesntExist()) {
            $problemi[] = 'Nessun Super Admin attivo: creane uno con «php artisan pescheria:admin».';
        }

        $this->riga('Punti vendita', (string) Store::count());
        $this->riga('Opportunità', (string) Opportunity::count());

        if ($email = $this->option('email')) {
            $this->newLine();
            $this->components->info("Account {$email}");

            $utente = User::withTrashed()->where('email', $email)->first();

            if (! $utente) {
                $this->riga('Esiste', 'NO');
                $problemi[] = "L'account {$email} non esiste: verifica l'indirizzo o crealo.";
            } else {
                $this->riga('Esiste', 'sì');
                $this->riga('Ruolo', $utente->role->label());
                $this->riga('Attivo', $utente->is_active ? 'sì' : 'NO — il login viene rifiutato');
                $this->riga('Eliminato', $utente->trashed() ? 'SÌ — il login viene rifiutato' : 'no');
                $this->riga('Cambio password richiesto', $utente->must_change_password ? 'sì' : 'no');
                $this->riga('Ultimo accesso', $utente->last_login_at?->format('d/m/Y H:i') ?? 'mai');

                if (! $utente->is_active || $utente->trashed()) {
                    $problemi[] = "L'account {$email} non è utilizzabile: riattivalo dalla gestione utenti.";
                }

                if ($password = $this->option('password')) {
                    $corrisponde = Hash::check($password, $utente->password);

                    $this->riga('Password fornita', $corrisponde ? 'corrisponde' : 'NON corrisponde');

                    if (! $corrisponde) {
                        $problemi[] = 'La password non corrisponde a quella salvata. Reimpostala con: '
                            ."php artisan pescheria:admin --email={$email} --password=NUOVA_PASSWORD";
                    }

                    // Ripete esattamente il controllo del login, filtro su is_active compreso.
                    $accettato = Auth::validate([
                        'email' => $email,
                        'password' => $password,
                        'is_active' => true,
                    ]);

                    $this->riga('Esito del login', $accettato ? 'sarebbe accettato' : 'sarebbe RIFIUTATO');
                }
            }
        }

        $this->newLine();

        if ($problemi === []) {
            $this->components->info('Nessun problema rilevato.');

            return self::SUCCESS;
        }

        $this->components->warn('Da sistemare:');

        foreach ($problemi as $problema) {
            $this->line('  • '.$problema);
        }

        return self::SUCCESS;
    }

    private function riga(string $etichetta, string $valore): void
    {
        $this->line(sprintf('  %-28s %s', $etichetta, $valore));
    }
}
