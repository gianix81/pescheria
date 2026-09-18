<?php

namespace App\Policies;

use App\Models\User;

/**
 * Anagrafiche (utenti, punti vendita, prodotti): nell'MVP le gestisce il Tecnico
 * (assunzione A5). Nessun quarto ruolo "Admin".
 */
class AnagraficaPolicy
{
    public function manage(User $user): bool
    {
        return $user->isTecnico();
    }
}
