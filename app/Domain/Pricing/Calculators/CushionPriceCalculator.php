<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Sedák — vzorec stanovil majitel dílny (odpověď na zadání §13.1).
 *
 * Záludnost: ROUNO_025CM2 je cena za 100 jednotek po 0,25 cm², takže
 * sedák 120 × 80 cm s rounem stojí při sazbě 38 Kč jen za rouno 14 592 Kč.
 * Výši určuje dílna sazbou v ceníku, vzorec je převzatý beze změny.
 */
class CushionPriceCalculator implements PriceCalculator
{
    /** Tloušťka jedné vrstvy molitanu [cm], viz ceníková položka MOLITAN_2CM. */
    private const float FOAM_LAYER_CM = 2.0;

    /** Plocha jedné účtovací jednotky rouna [cm²], viz ROUNO_025CM2. */
    private const float FELT_UNIT_CM2 = 0.25;

    /** Sazba ROUNO_025CM2 platí za tolik jednotek. */
    private const int FELT_UNITS_PER_RATE = 100;

    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof CushionParameters) {
            throw new InvalidArgumentException('Kalkulátor sedáku vyžaduje CushionParameters.');
        }

        $areaCm2 = $parameters->width * $parameters->height;
        $areaM2 = $areaCm2 / 10_000;

        $lines = [
            new PriceLine('Ušití sedáku', $prices->var('USITI_SEDAK')),
            new PriceLine('Příprava', $areaM2 * $prices->var('PRIPRAVA')),
        ];

        if ($parameters->foam) {
            $layers = max(1, ceil($parameters->depth / self::FOAM_LAYER_CM));
            $lines[] = new PriceLine('Molitan', $areaM2 * $layers * $prices->var('MOLITAN_2CM'));
        }

        if ($parameters->felt) {
            $units = $areaCm2 / self::FELT_UNIT_CM2;
            $lines[] = new PriceLine('Rouno', $units / self::FELT_UNITS_PER_RATE * $prices->var('ROUNO_025CM2'));
        }

        if ($parameters->ties) {
            $lines[] = new PriceLine('Úvazy', $prices->var('USITI_UVAZ'));
        }

        return PriceResult::fromLines($lines);
    }
}
