<?php

namespace Database\Factories;

use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\Response;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Response>
 */
class ResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'store_id' => Store::factory(),
            'status' => ResponseStatus::NON_COMPILATA,
            'packages' => 0,
            'kg' => 0,
            'committed_packages' => 0,
        ];
    }

    public function acquisto(int $colli = 2): static
    {
        return $this->state(function (array $attrs) use ($colli) {
            $opportunity = Opportunity::find($attrs['opportunity_id']);

            return [
                'status' => ResponseStatus::INVIATA_ACQUISTO,
                'packages' => $colli,
                'kg' => round($colli * (float) ($opportunity?->kg_per_package ?? 5), 3),
                'committed_packages' => $opportunity?->isLimited() ? $colli : 0,
                'submitted_at' => now(),
            ];
        });
    }

    public function rifiuto(): static
    {
        return $this->state(fn () => [
            'status' => ResponseStatus::INVIATA_RIFIUTO,
            'packages' => 0,
            'kg' => 0,
            'submitted_at' => now(),
        ]);
    }
}
