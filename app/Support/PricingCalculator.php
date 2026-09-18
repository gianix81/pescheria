<?php

namespace App\Support;

/**
 * Calcoli di prezzo secondo le definizioni obbligatorie del capitolato (§5.1).
 *
 * Attenzione alla differenza, che nel processo WhatsApp era confusa:
 *  - RICARICO  = utile / costo          (quanto "carico" sopra il prezzo d'acquisto)
 *  - MARGINE   = utile / prezzo netto   (quanto dell'incasso netto resta)
 */
final class PricingCalculator
{
    /** Prezzo di vendita al netto dell'IVA. */
    public static function netSalePrice(float $grossSalePrice, float $vatRatePercent): float
    {
        $divisor = 1 + ($vatRatePercent / 100);

        return $divisor > 0 ? $grossSalePrice / $divisor : 0.0;
    }

    /** ((netto - acquisto) / acquisto) × 100 */
    public static function markupPercent(float $purchasePrice, float $grossSalePrice, float $vatRatePercent): ?float
    {
        if ($purchasePrice <= 0) {
            return null;
        }

        $net = self::netSalePrice($grossSalePrice, $vatRatePercent);

        return round((($net - $purchasePrice) / $purchasePrice) * 100, 2);
    }

    /** ((netto - acquisto) / netto) × 100 */
    public static function marginPercent(float $purchasePrice, float $grossSalePrice, float $vatRatePercent): ?float
    {
        $net = self::netSalePrice($grossSalePrice, $vatRatePercent);

        if ($net <= 0) {
            return null;
        }

        return round((($net - $purchasePrice) / $net) * 100, 2);
    }

    /** @return array{net: float, markup: ?float, margin: ?float} */
    public static function all(float $purchasePrice, float $grossSalePrice, float $vatRatePercent): array
    {
        return [
            'net' => round(self::netSalePrice($grossSalePrice, $vatRatePercent), 4),
            'markup' => self::markupPercent($purchasePrice, $grossSalePrice, $vatRatePercent),
            'margin' => self::marginPercent($purchasePrice, $grossSalePrice, $vatRatePercent),
        ];
    }
}
