<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $specie = fake()->randomElement([
            'Orata', 'Branzino', 'Cozze', 'Vongole', 'Gambero rosso', 'Polpo',
            'Salmone', 'Tonno', 'Sogliola', 'Calamaro',
        ]);

        return [
            'article_code' => 'ART'.fake()->unique()->numberBetween(10000, 99999),
            'plu' => (string) fake()->unique()->numberBetween(1000, 9999),
            'description' => $specie.' '.fake()->randomElement(['fresco', 'allevamento', 'pescato', 'decongelato']),
            'long_description' => 'Prodotto ittico '.strtolower($specie).' selezionato per il reparto pescheria.',
            'category' => fake()->randomElement(['Pesce', 'Molluschi', 'Crostacei']),
            'origin' => fake()->randomElement(['Italia', 'Grecia', 'Spagna', 'Francia']),
            'fao_zone' => fake()->randomElement(['FAO 37.1.3', 'FAO 37.2.1', 'FAO 27']),
            'production_method' => fake()->randomElement(['Pescato in mare', 'Allevamento']),
            'caliber' => fake()->randomElement(['300/400 g', '400/600 g', '1/2 kg']),
            'unit_of_measure' => 'KG',
            'vat_rate' => 10.00,
            'default_kg_per_package' => fake()->randomElement([4.0, 5.0, 6.0, 8.0, 10.0]),
            'is_active' => true,
        ];
    }
}
