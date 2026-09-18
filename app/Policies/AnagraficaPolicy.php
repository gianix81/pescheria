<?php

namespace App\Policies;

use App\Models\User;

/**
 * Anagrafiche (utenti, punti vendita, prodotti): nell'MVP le gestisce il Tecnico
 * (assunzione A5). Nessun quarto ruolo "Admin".
 */
class AnagraficaPolicy
{
    /** Creazione e modifica di utenti, punti vendita e prodotti. */
    public function manage(User $user): bool
    {
        return $user->isTecnico();      // include il Super Admin
    }

    /**
     * Azioni irreversibili o delicate sugli account: eliminazione, ripristino,
     * reimpostazione della password. Riservate al Super Admin.
     */
    public function manageAccounts(User $user): bool
    {
        return $user->isAdmin();
    }
}
