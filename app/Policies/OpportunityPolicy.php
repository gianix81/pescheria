<?php

namespace App\Policies;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    /** Il CR vede solo opportunità pubblicate e destinate al proprio punto vendita. */
    public function view(User $user, Opportunity $opportunity): bool
    {
        if ($user->isCapoReparto()) {
            return in_array($opportunity->status, OpportunityStatus::visibleToStores(), true)
                && $user->store_id !== null
                && $opportunity->stores()->where('stores.id', $user->store_id)->exists();
        }

        return $user->isBuyer() || $user->isTecnico();
    }

    public function viewAny(User $user): bool
    {
        return true;    // il perimetro è applicato da Opportunity::visibleTo()
    }

    public function create(User $user): bool
    {
        return $user->isBuyer();
    }

    /** Il Buyer modifica bozze e opportunità da correggere. */
    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer() && $opportunity->status->isEditableByBuyer();
    }

    /** Modifica di un'opportunità già pubblicata: consentita al Buyer, mai silenziosa. */
    public function updatePublished(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer()
            && in_array($opportunity->status, [OpportunityStatus::PROGRAMMATA, OpportunityStatus::APERTA], true);
    }

    public function submitForReview(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer() && $opportunity->status->isEditableByBuyer();
    }

    public function review(User $user, Opportunity $opportunity): bool
    {
        return $user->isTecnico() && $opportunity->status === OpportunityStatus::IN_VERIFICA;
    }

    public function close(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer()
            && in_array($opportunity->status, [OpportunityStatus::PROGRAMMATA, OpportunityStatus::APERTA, OpportunityStatus::SCADUTA], true);
    }

    public function cancel(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer() && ! in_array($opportunity->status, OpportunityStatus::terminal(), true);
    }

    public function duplicate(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer();
    }

    /** Solo Buyer e Tecnico vedono le risposte di tutti i punti vendita. */
    public function viewResponses(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer() || $user->isTecnico();
    }

    public function export(User $user): bool
    {
        return $user->isBuyer() || $user->isTecnico();
    }

    public function uploadMedia(User $user, Opportunity $opportunity): bool
    {
        return $user->isBuyer();
    }
}
