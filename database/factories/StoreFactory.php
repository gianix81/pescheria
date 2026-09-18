<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'PV'.fake()->unique()->numberBetween(100, 999),
            'name' => 'Punto Vendita '.fake()->city(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => strtoupper(fake()->lexify('??')),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('0## ######'),
            'is_active' => true,
        ];
    }
}
