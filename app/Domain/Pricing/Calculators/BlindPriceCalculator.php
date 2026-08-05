<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\BlindParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Římská roleta — pevná cenová tabulka podle pásem šířka × výška (zadání §7.5)
 * + příplatky. `mechanism`, `cells` a výšky polí cenu neovlivňují.
 */
class BlindPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof BlindParameters) {
            throw new InvalidArgumentException('Kalkulátor rolety vyžaduje BlindParameters.');
        }

        $lines = [
            new PriceLine(
                'Roleta dle rozměru',
                $prices->blindPrice($parameters->width, $parameters->height),
            ),
        ];

        if ($parameters->tunnels) {
            $lines[] = new PriceLine('Odšité tunýlky', $prices->var('TUNYLKY_ROLETA'));
        }

        if ($parameters->finishing) {
            $lines[] = new PriceLine('Kompletace', $prices->var('KOMPLET_ROLETA'));
        }

        return PriceResult::fromLines($lines);
    }
}
