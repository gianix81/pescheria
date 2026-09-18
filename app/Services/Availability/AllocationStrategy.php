<?php

namespace App\Services\Availability;

use App\Exceptions\InsufficientStockException;
use App\Models\Opportunity;

/**
 * Politica di assegnazione della disponibilità limitata (assunzione MVP A4).
 *
 * Sostituire questa implementazione (config/pescheria.php → allocation_strategy) permette
 * di passare a una ripartizione manuale o proporzionale senza toccare workflow e UI.
 */
interface AllocationStrategy
{
    /**
     * Decide quanti colli assegnare a una risposta, dato lo stock dell'opportunità
     * già bloccata in transazione.
     *
     * @param  Opportunity  $lockedOpportunity  riga già bloccata con lockForUpdate()
     * @param  int  $alreadyCommittedByResponse  colli attualmente impegnati dalla stessa risposta
     * @param  int  $requested  colli richiesti ora
     * @return int colli assegnati
     *
     * @throws InsufficientStockException
     */
    public function allocate(Opportunity $lockedOpportunity, int $alreadyCommittedByResponse, int $requested): int;
}
