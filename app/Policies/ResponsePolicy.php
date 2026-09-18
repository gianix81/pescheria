<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\Response;
use App\Models\User;

class ResponsePolicy
{
    /**
     * Visibilità delle risposte.
     *
     * Scelta di prodotto: dentro un'opportunità i punti vendita vedono anche le
     * quantità ordinate dagli altri, per creare emulazione fra i reparti. La
     * condizione resta essere destinatari della stessa opportunità: un punto
     * vendita non destinatario non vede nulla.
     *
     * Vedere non significa poter agire: modificare resta possibile solo sulla
     * propria risposta (metodo `update`).
     */
    public function view(User $user, Response $response): bool
    {
        if ($user->isCapoReparto()) {
            return $user->store_id !== null
                && $response->opportunity->stores()->where('stores.id', $user->store_id)->exists();
        }

        return $user->isBuyer() || $user->isTecnico();
    }

    /** Solo la propria risposta è modificabile, anche se si vedono tutte. */
    public function update(User $user, Response $response): bool
    {
        return $user->isCapoReparto()
            && $user->store_id !== null
            && $response->store_id === $user->store_id;
    }

    /** Solo il CR del punto vendita destinatario può rispondere. */
    public function respond(User $user, Opportunity $opportunity): bool
    {
        return $user->isCapoReparto()
            && $user->store_id !== null
            && $opportunity->stores()->where('stores.id', $user->store_id)->exists();
    }

    public function reopen(User $user, Response $response): bool
    {
        return $user->isTecnico();
    }

    /** Correzione eccezionale dopo la scadenza: solo Buyer, con motivazione. */
    public function override(User $user, Response $response): bool
    {
        return $user->isBuyer();
    }
}
