<?php

namespace Database\Factories;

use App\Enums\AvailabilityType;
use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\User;
use App\Support\PricingCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory();
        $purchase = fake()->randomFloat(2, 3, 18);
        $vat = 10.00;
        $sale = round($purchase * 1.45 * (1 + $vat / 100), 2);
        $pricing = PricingCalculator::all($purchase, $sale, $vat);

        return [
            'reference' => fn () => Opportunity::nextReference(),
            'product_id' => $product,
            'article_code' => fn (array $attrs) => Product::find($attrs['product_id'])?->article_code ?? 'ART00000',
            'portal_product_code' => fn (array $attrs) => Product::find($attrs['product_id'])?->portal_code,
            'plu' => fn (array $attrs) => Product::find($attrs['product_id'])?->plu,
            'description' => fn (array $attrs) => Product::find($attrs['product_id'])?->description ?? 'Articolo',
            'long_description' => null,
            'category' => 'Pesce',
            'origin' => 'Italia',
            'fao_zone' => 'FAO 37.1.3',
            'production_method' => 'Pescato in mare',
            'caliber' => '400/600 g',
            'title' => fn (array $attrs) => 'Offerta '.($attrs['description'] ?? 'ittica'),
            'commercial_description' => 'Prodotto fresco disponibile in quantità limitata.',
            'technical_notes' => null,
            'logistics_notes' => null,
            'order_unit' => 'COLLO',
            'kg_per_package' => fake()->randomElement([4.0, 5.0, 6.0]),
            'price_unit' => 'EUR/KG',
            'purchase_price' => $purchase,
            'sale_price_gross' => $sale,
            'vat_rate' => $vat,
            'markup_percent' => $pricing['markup'],
            'margin_percent' => $pricing['margin'],
            'min_lot' => 1,
            'order_multiple' => 1,
            'quick_quantities' => [1, 2, 3, 4, 5, 6],
            'availability_type' => AvailabilityType::APERTA,
            'total_packages' => null,
            'committed_packages' => 0,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHours(6),
            'delivery_date' => now()->addDays(2)->toDateString(),
            'status' => OpportunityStatus::BOZZA,
            'created_by' => User::factory()->buyer(),
        ];
    }

    /**
     * Ricarico e margine si ricalcolano sempre dai prezzi finali.
     *
     * Senza, sovrascrivendo i prezzi nel create() resterebbero quelli generati
     * a caso: i test vedrebbero percentuali che non corrispondono ai prezzi,
     * esattamente ciò che nell'applicazione non può accadere perché il ricalcolo
     * è nel modello.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Opportunity $opportunity) {
            $opportunity->recalculatePricing();
        });
    }

    public function aperta(): static
    {
        return $this->state(fn () => [
            'status' => OpportunityStatus::APERTA,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHours(6),
            'published_at' => now()->subHour(),
        ]);
    }

    public function inVerifica(): static
    {
        return $this->state(fn () => ['status' => OpportunityStatus::IN_VERIFICA, 'submitted_at' => now()]);
    }

    public function programmata(): static
    {
        return $this->state(fn () => [
            'status' => OpportunityStatus::PROGRAMMATA,
            'opens_at' => now()->addHours(2),
            'closes_at' => now()->addHours(12),
        ]);
    }

    public function scaduta(): static
    {
        return $this->state(fn () => [
            'status' => OpportunityStatus::SCADUTA,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->subHour(),
        ]);
    }

    public function limitata(int $colli = 20): static
    {
        return $this->state(fn () => [
            'availability_type' => AvailabilityType::LIMITATA,
            'total_packages' => $colli,
            'committed_packages' => 0,
        ]);
    }
}
