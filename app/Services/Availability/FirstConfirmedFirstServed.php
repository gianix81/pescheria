<?php

namespace App\Services\Availability;

use App\Exceptions\InsufficientStockException;
use App\Models\Opportunity;

/**
 * "Primo che conferma, primo servito": lo stock è impegnato solo all'invio definitivo
 * (mai al salvataggio della bozza) e la modifica muove soltanto la differenza.
 */
class FirstConfirmedFirstServed implements AllocationStrategy
{
    public function allocate(Opportunity $lockedOpportunity, int $alreadyCommittedByResponse, int $requested): int
    {
        if (! $lockedOpportunity->isLimited()) {
            return $requested;      // disponibilità aperta: nessun limite globale
        }

        $total = (int) $lockedOpportunity->total_packages;
        $committedByOthers = (int) $lockedOpportunity->committed_packages - $alreadyCommittedByResponse;
        $remainingForThisResponse = max(0, $total - $committedByOthers);

        if ($requested > $remainingForThisResponse) {
            throw new InsufficientStockException($remainingForThisResponse, $requested);
        }

        return $requested;
    }
}
