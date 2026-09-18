<?php

namespace App\Console\Commands;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Services\OpportunityWorkflowService;
use Illuminate\Console\Command;

/** PROGRAMMATA → APERTA. Idempotente: può girare ogni minuto senza effetti collaterali. */
class OpenScheduledOpportunities extends Command
{
    protected $signature = 'opportunita:apri';

    protected $description = 'Apre le opportunità programmate la cui ora di apertura è arrivata';

    public function handle(OpportunityWorkflowService $workflow): int
    {
        $aperte = 0;

        Opportunity::where('status', OpportunityStatus::PROGRAMMATA)
            ->where('opens_at', '<=', now())
            ->orderBy('opens_at')
            ->chunkById(100, function ($gruppo) use ($workflow, &$aperte) {
                foreach ($gruppo as $opportunita) {
                    if ($workflow->open($opportunita)) {
                        $aperte++;
                        $this->line("Aperta {$opportunita->reference}");
                    }
                }
            });

        $this->info("Opportunità aperte: {$aperte}");

        return self::SUCCESS;
    }
}
