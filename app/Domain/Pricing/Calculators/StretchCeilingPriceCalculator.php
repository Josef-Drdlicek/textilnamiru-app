<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\StretchCeilingParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Vypínaný strop — plocha × VYPINANY_PODHLED, LED pásek × LED_PASEK,
 * doprava × DOPRAVA. Sazby a jednotky zavedl majitel dílny v ceníku.
 */
class StretchCeilingPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof StretchCeilingParameters) {
            throw new InvalidArgumentException('Kalkulátor stropu vyžaduje StretchCeilingParameters.');
        }

        $lines = [
            new PriceLine('Vypínaný strop', $parameters->areaM2() * $prices->var('VYPINANY_PODHLED')),
        ];

        if ($parameters->ledLength > 0) {
            $lines[] = new PriceLine('LED pásek', $parameters->ledLength * $prices->var('LED_PASEK'));
        }

        if ($parameters->distance > 0) {
            $lines[] = new PriceLine('Doprava', $parameters->distance * $prices->var('DOPRAVA'));
        }

        return PriceResult::fromLines($lines);
    }
}
