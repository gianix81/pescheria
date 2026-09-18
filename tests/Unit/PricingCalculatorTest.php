<?php

namespace Tests\Unit;

use App\Support\PricingCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    #[Test]
    public function calcola_prezzo_netto_scorporando_iva(): void
    {
        $this->assertEqualsWithDelta(7.2545, PricingCalculator::netSalePrice(7.98, 10), 0.0001);
        $this->assertEqualsWithDelta(6.5410, PricingCalculator::netSalePrice(7.98, 22), 0.0001);
    }

    /** L'esempio del capitolato: acquisto 5,00 — vendita 7,98 con IVA 10% → ricarico ~45%, margine ~31%. */
    #[Test]
    public function esempio_del_capitolato_distingue_ricarico_e_margine(): void
    {
        $this->assertEqualsWithDelta(45.09, PricingCalculator::markupPercent(5.00, 7.98, 10), 0.01);
        $this->assertEqualsWithDelta(31.08, PricingCalculator::marginPercent(5.00, 7.98, 10), 0.01);
    }

    #[Test]
    #[DataProvider('aliquote')]
    public function ricarico_e_margine_con_diverse_aliquote(float $acquisto, float $vendita, float $iva, float $ricarico, float $margine): void
    {
        $this->assertEqualsWithDelta($ricarico, PricingCalculator::markupPercent($acquisto, $vendita, $iva), 0.05);
        $this->assertEqualsWithDelta($margine, PricingCalculator::marginPercent($acquisto, $vendita, $iva), 0.05);
    }

    public static function aliquote(): array
    {
        return [
            'IVA 4%' => [10.00, 15.60, 4.0, 50.00, 33.33],
            'IVA 10%' => [10.00, 16.50, 10.0, 50.00, 33.33],
            'IVA 22%' => [10.00, 18.30, 22.0, 50.00, 33.33],
        ];
    }

    #[Test]
    public function il_ricarico_e_sempre_maggiore_del_margine_quando_ce_utile(): void
    {
        $ricarico = PricingCalculator::markupPercent(5.00, 7.98, 10);
        $margine = PricingCalculator::marginPercent(5.00, 7.98, 10);

        $this->assertGreaterThan($margine, $ricarico);
    }

    #[Test]
    public function prezzo_acquisto_zero_non_produce_divisione_per_zero(): void
    {
        $this->assertNull(PricingCalculator::markupPercent(0.0, 7.98, 10));
        $this->assertSame(100.0, PricingCalculator::marginPercent(0.0, 7.98, 10));
    }

    #[Test]
    public function vendita_sotto_costo_produce_valori_negativi(): void
    {
        $this->assertLessThan(0, PricingCalculator::markupPercent(10.00, 8.80, 10));
        $this->assertLessThan(0, PricingCalculator::marginPercent(10.00, 8.80, 10));
    }
}
