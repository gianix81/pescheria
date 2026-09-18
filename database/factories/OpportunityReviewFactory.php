<?php

namespace Database\Factories;

use App\Enums\ReviewOutcome;
use App\Models\Opportunity;
use App\Models\OpportunityReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpportunityReview>
 */
class OpportunityReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'reviewer_id' => User::factory()->tecnico(),
            'outcome' => ReviewOutcome::APPROVATA,
            'notes' => null,
        ];
    }
}
