<?php

namespace App\Console\Commands;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Services\OpportunityWorkflowService;
use App\Support\Sistema;
use Illuminate\Console\Command;

/** APERTA → SCADUTA. Idempotente. */
class ExpireOpportunities extends Command
{
    protected $signature = 'opportunita:scadi';

    protected $description = 'Porta in stato SCADUTA le opportunità il cui termine è superato';

    public function handle(OpportunityWorkflowService $workflow): int
    {
        Sistema::registraEsecuzioneScheduler();

        $scadute = 0;

        Opportunity::where('status', OpportunityStatus::APERTA)
            ->where('closes_at', '<=', now())
            ->orderBy('closes_at')
            ->chunkById(100, function ($gruppo) use ($workflow, &$scadute) {
                foreach ($gruppo as $opportunita) {
                    if ($workflow->expire($opportunita)) {
                        $scadute++;
                        $this->line("Scaduta {$opportunita->reference}");
                    }
                }
            });

        $this->info("Opportunità scadute: {$scadute}");

        return self::SUCCESS;
    }
}
