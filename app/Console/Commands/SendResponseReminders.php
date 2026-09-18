<?php

namespace App\Console\Commands;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Solleciti ai CR che non hanno ancora risposto, alle soglie configurate
 * (NOTIFY_REMINDER_MINUTES, per esempio 120 e 30 minuti prima della scadenza).
 *
 * La chiave di idempotenza include la soglia: lo stesso promemoria non parte due volte.
 */
class SendResponseReminders extends Command
{
    protected $signature = 'opportunita:solleciti';

    protected $description = 'Invia i promemoria ai capi reparto che non hanno ancora risposto';

    public function handle(NotificationService $notifiche): int
    {
        $soglie = config('pescheria.notifiche.reminder_minutes', []);
        $inviati = 0;

        foreach ($soglie as $minuti) {
            // Finestra di un minuto attorno alla soglia: lo scheduler gira ogni minuto.
            $da = now()->addMinutes($minuti);
            $a = $da->copy()->addMinute();

            Opportunity::where('status', OpportunityStatus::APERTA)
                ->whereBetween('closes_at', [$da, $a])
                ->get()
                ->each(function (Opportunity $opportunita) use ($notifiche, $minuti, &$inviati) {
                    $inviati += $notifiche->remindMissing($opportunita, "auto-{$minuti}min");
                });
        }

        $this->info("Solleciti inviati: {$inviati}");

        return self::SUCCESS;
    }
}
