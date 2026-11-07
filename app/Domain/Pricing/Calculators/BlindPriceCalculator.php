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
 * Římská roleta — model ceny stanovil majitel dílny: kompletace + tunýlky
 * za metr šířky + příprava za metr výšky + ušití každého tunýlku.
 * Nahrazuje původní tabulku pásem šířka × výška (zadání §7.5).
 * `mechanism`, `cells` a výšky polí cenu neovlivňují.
 */
class BlindPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof BlindParameters) {
            throw new InvalidArgumentException('Kalkulátor rolety vyžaduje BlindParameters.');
        }

        return PriceResult::fromLines([
            new PriceLine('Kompletace', $prices->var('KOMPLET_ROLETA')),
            new PriceLine('Odšité tunýlky', ($parameters->width / 100) * $prices->var('TUNYLKY_ROLETA')),
            new PriceLine('Příprava', ($parameters->height / 100) * $prices->var('PRIPRAVA')),
            new PriceLine('Ušití tunýlků', $parameters->tunnels * $prices->var('USITI_ZALOZKA')),
        ]);
    }
}
