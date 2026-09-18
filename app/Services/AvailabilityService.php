<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Opportunity;
use App\Models\Response;
use App\Services\Availability\AllocationStrategy;
use Illuminate\Support\Facades\DB;

/**
 * Unico punto in cui si tocca lo stock di un'opportunità a disponibilità limitata.
 *
 * Deve essere invocato SEMPRE dentro una transazione già aperta dal chiamante
 * (ResponseSubmissionService), dopo aver bloccato la riga dell'opportunità:
 * è il lock di riga a impedire la sovra-allocazione con invii simultanei.
 */
class AvailabilityService
{
    public function __construct(private readonly AllocationStrategy $strategy) {}

    /** Blocca la riga dell'opportunità per l'aggiornamento dello stock. */
    public function lock(Opportunity $opportunity): Opportunity
    {
        return Opportunity::whereKey($opportunity->getKey())->lockForUpdate()->firstOrFail();
    }

    /**
     * Porta l'impegno della risposta da `$response->committed_packages` a `$requested`.
     * Restituisce i colli effettivamente impegnati.
     *
     * @throws InsufficientStockException
     */
    public function applyCommitment(Opportunity $lockedOpportunity, Response $response, int $requested): int
    {
        $already = (int) $response->committed_packages;

        if (! $lockedOpportunity->isLimited()) {
            // Disponibilità aperta: nessun contatore globale da mantenere.
            $response->committed_packages = 0;

            return $requested;
        }

        $granted = $this->strategy->allocate($lockedOpportunity, $already, $requested);
        $delta = $granted - $already;

        if ($delta !== 0) {
            DB::table('opportunities')
                ->where('id', $lockedOpportunity->id)
                ->update([
                    'committed_packages' => DB::raw('committed_packages + '.$delta),
                    'updated_at' => now(),
                ]);

            $lockedOpportunity->committed_packages = (int) $lockedOpportunity->committed_packages + $delta;
        }

        $response->committed_packages = $granted;

        return $granted;
    }

    /** Libera tutto ciò che la risposta aveva impegnato (rifiuto, riapertura, annullamento). */
    public function release(Opportunity $lockedOpportunity, Response $response): void
    {
        $this->applyCommitment($lockedOpportunity, $response, 0);
    }

    /** Ricalcolo di servizio: riallinea il contatore alle risposte effettivamente inviate. */
    public function recomputeCommitted(Opportunity $opportunity): int
    {
        $sum = (int) $opportunity->responses()->sum('committed_packages');

        $opportunity->forceFill(['committed_packages' => $sum])->save();

        return $sum;
    }
}
