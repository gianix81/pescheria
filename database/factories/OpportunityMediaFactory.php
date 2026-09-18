<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OpportunityMedia>
 */
class OpportunityMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'type' => MediaType::IMAGE,
            'disk' => config('pescheria.media.disk'),
            'path' => 'opportunita/demo/'.Str::uuid().'.jpg',
            'original_name' => 'foto.jpg',
            'mime' => 'image/jpeg',
            'size' => 250_000,
            'sort_order' => 0,
            'scan_status' => 'SKIPPED',
        ];
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'type' => MediaType::VIDEO,
            'mime' => 'video/mp4',
            'path' => 'opportunita/demo/'.Str::uuid().'.mp4',
            'original_name' => 'video.mp4',
            'size' => 8_000_000,
        ]);
    }
}
