<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\Response;
use App\Models\User;

class ResponsePolicy
{
    /** Un CR vede solo le risposte del proprio punto vendita. */
    public function view(User $user, Response $response): bool
    {
        if ($user->isCapoReparto()) {
            return $user->store_id !== null && $response->store_id === $user->store_id;
        }

        return $user->isBuyer() || $user->isTecnico();
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
