<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Crea (o ripristina) un Super Admin dalla console.
 *
 * È la via d'accesso garantita in produzione: non dipende dal seeder, funziona
 * anche su un database già popolato e riattiva un profilo disattivato o
 * eliminato. Serve quando nessuno riesce più a entrare.
 *
 *   php artisan pescheria:admin
 *   php artisan pescheria:admin --email=mario@azienda.it --password='...' --nome=Mario --cognome=Rossi
 */
class CreaSuperAdmin extends Command
{
    protected $signature = 'pescheria:admin
        {--email= : Email dell\'account}
        {--password= : Password; se omessa ne viene generata una}
        {--nome=Super : Nome}
        {--cognome=Admin : Cognome}
        {--forza-cambio : Obbliga a cambiare la password al primo accesso}';

    protected $description = 'Crea o ripristina un profilo Super Admin';

    public function handle(AuditService $audit): int
    {
        $email = $this->option('email') ?: $this->ask('Email del Super Admin');
        $password = $this->option('password') ?: Str::password(16, symbols: false);
        $generata = ! $this->option('password');

        $validatore = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email', 'max:190'],
                'password' => ['required', PasswordRule::min(10)->letters()->numbers()],
            ],
        );

        if ($validatore->fails()) {
            foreach ($validatore->errors()->all() as $errore) {
                $this->error($errore);
            }

            return self::FAILURE;
        }

        $utente = User::withTrashed()->firstOrNew(['email' => $email]);
        $esisteva = $utente->exists;

        if ($utente->trashed()) {
            $utente->restore();
            $this->warn('Il profilo era stato eliminato: è stato ripristinato.');
        }

        $utente->fill([
            'first_name' => $this->option('nome'),
            'last_name' => $this->option('cognome'),
            'role' => Role::ADMIN,
            'store_id' => null,
            'is_active' => true,
        ]);

        $utente->password = $password;
        $utente->must_change_password = (bool) $this->option('forza-cambio');
        $utente->save();

        $audit->log($esisteva ? 'user.admin_reset' : 'user.admin_created', $utente, ['email' => $utente->email]);

        $this->newLine();
        $this->info($esisteva ? 'Profilo aggiornato a Super Admin.' : 'Super Admin creato.');
        $this->table(['Campo', 'Valore'], [
            ['Email', $utente->email],
            ['Password', $generata ? $password : '(quella indicata)'],
            ['Ruolo', $utente->role->label()],
            ['Cambio obbligatorio', $utente->must_change_password ? 'sì' : 'no'],
        ]);

        if ($generata) {
            $this->warn('Annota la password adesso: non sarà più recuperabile.');
        }

        return self::SUCCESS;
    }
}
