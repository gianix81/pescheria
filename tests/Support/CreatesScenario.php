<?php

namespace Tests\Support;

use App\Enums\AvailabilityType;
use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\Store;
use App\Models\User;

/** Scorciatoie per costruire lo scenario tipico: un Buyer, un Tecnico, N punti vendita con CR. */
trait CreatesScenario
{
    protected function buyer(): User
    {
        return User::factory()->buyer()->create();
    }

    protected function tecnico(): User
    {
        return User::factory()->tecnico()->create();
    }

    /** @return array{0: Store, 1: User} */
    protected function storeWithCr(?string $code = null): array
    {
        $store = Store::factory()->create($code ? ['code' => $code] : []);
        $cr = User::factory()->capoReparto($store)->create();

        return [$store, $cr];
    }

    /** Opportunità aperta e destinata ai punti vendita indicati. */
    protected function openOpportunity(array $stores, array $attributes = []): Opportunity
    {
        $opportunity = Opportunity::factory()->aperta()->create($attributes + [
            'created_by' => $this->buyer()->id,
        ]);

        $opportunity->stores()->sync(collect($stores)->pluck('id')->all());

        return $opportunity->fresh(['stores']);
    }

    protected function limitedOpportunity(array $stores, int $colli, array $attributes = []): Opportunity
    {
        return $this->openOpportunity($stores, $attributes + [
            'availability_type' => AvailabilityType::LIMITATA,
            'total_packages' => $colli,
            'committed_packages' => 0,
            'status' => OpportunityStatus::APERTA,
        ]);
    }
}
