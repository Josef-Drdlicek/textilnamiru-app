<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\CushionParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Sedák — v původní aplikaci se cení PŘESNĚ jako polštář: hloubka, zip
 * ani sedákové ceníkové položky (USITI_SEDAK, MOLITAN_2CM, ROUNO_025CM2)
 * do ceny nevstupují. Vědomě replikováno 1:1; správný vzorec je otevřená
 * otázka na dílnu (zadání §7.4 a §13.1) — po odpovědi stačí upravit tuto třídu.
 */
class CushionPriceCalculator implements PriceCalculator
{
    public function __construct(
        private readonly PillowPriceCalculator $pillowCalculator,
    ) {}

    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof CushionParameters) {
            throw new InvalidArgumentException('Kalkulátor sedáku vyžaduje CushionParameters.');
        }

        return PriceResult::fromLines(
            $this->pillowCalculator->buildLines($parameters->width, $parameters->height, $parameters->stuffing, $prices),
        );
    }
}
