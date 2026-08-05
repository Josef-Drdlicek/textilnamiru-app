<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Záclona — vzorec dle zadání §7.1.
 *
 * `kanyr`, `kanyr_height` a `right_left` cenu neovlivňují (jen výroba/nákres).
 */
class CurtainPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof CurtainParameters) {
            throw new InvalidArgumentException('Kalkulátor záclony vyžaduje CurtainParameters.');
        }

        // Spotřeba materiálu [m]: nabraná šířka dle poměru řasení + 20 cm na založení krajů.
        $consumed = (($parameters->width * $parameters->ratio) + 20) / 100;
        $heightMeters = $parameters->height / 100;

        $lines = [
            new PriceLine('Příprava', $consumed * $prices->var('PRIPRAVA')),
            new PriceLine('Řasící stuha (materiál)', $consumed * $prices->ribbonPricePerMeter($parameters->ribbonCode)),
            new PriceLine('Našití řasící stuhy', $consumed * $prices->var('RASICI_STUHA')),
            new PriceLine('Lemování boků', 2 * $heightMeters * $prices->var('LEMOVANI')),
            new PriceLine('Kordovací destička', $prices->var('KORDOVANI')),
            new PriceLine('Žehlení, srážení', $consumed * $heightMeters * $prices->var('ZEHLENI')),
        ];

        if ($parameters->corners) {
            $lines[] = new PriceLine('Rohová olůvka 13g', 2 * $prices->var('OLUVKO_13G'));
        }

        if ($parameters->bottom) {
            $lines[] = new PriceLine('Průběžné olovo', $consumed * $prices->var('OLUVKO'));
            $lines[] = new PriceLine('Všití olova', $consumed * $prices->var('VSITI_OLUVKO'));
        }

        if ($parameters->narasit) {
            $lines[] = new PriceLine('Řasení', $consumed * $prices->var('RASENI'));
        }

        if ($parameters->snails) {
            $lines[] = new PriceLine('Navlečení háčků', ceil($consumed / 0.08) * $prices->var('SNECEK'));
        }

        return PriceResult::fromLines($lines, consumedMeters: $consumed);
    }
}
