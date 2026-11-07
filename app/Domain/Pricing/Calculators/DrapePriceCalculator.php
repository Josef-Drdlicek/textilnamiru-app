<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Calculators\Concerns\AddsKanyrLine;
use App\Domain\Pricing\Parameters\DrapeParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Závěs — vzorec dle zadání §7.2. Oproti zácloně navíc ušití spodní záložky,
 * těžší rohová olůvka (50g) a sešití dílů u úzkého materiálu. Příplatky
 * za zalomení a kanýr doplnil majitel dílny.
 *
 * `raport`, `right_left` a `bottom` cenu neovlivňují (jen výroba/nákres).
 */
class DrapePriceCalculator implements PriceCalculator
{
    use AddsKanyrLine;

    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof DrapeParameters) {
            throw new InvalidArgumentException('Kalkulátor závěsu vyžaduje DrapeParameters.');
        }

        $consumed = (($parameters->width * $parameters->ratio) + 20) / 100;
        $heightMeters = $parameters->height / 100;

        $lines = [
            new PriceLine('Příprava', $consumed * $prices->var('PRIPRAVA')),
            new PriceLine('Řasící stuha (materiál)', $consumed * $prices->ribbonPricePerMeter($parameters->ribbonCode)),
            new PriceLine('Našití řasící stuhy', $consumed * $prices->var('RASICI_STUHA')),
            new PriceLine('Lemování boků', 2 * $heightMeters * $prices->var('LEMOVANI')),
            new PriceLine('Kordovací destička', $prices->var('KORDOVANI')),
            new PriceLine('Žehlení, srážení', $consumed * $heightMeters * $prices->var('ZEHLENI')),
            new PriceLine('Ušití spodní záložky', $consumed * $prices->var('USITI_ZALOZKA')),
        ];

        if ($parameters->corners) {
            $lines[] = new PriceLine('Rohová olůvka 50g', 2 * $prices->var('OLUVKO_50G'));
        }

        if ($parameters->narasit) {
            $lines[] = new PriceLine('Řasení', $consumed * $prices->var('RASENI'));
        }

        if ($parameters->snails) {
            $lines[] = new PriceLine('Navlečení háčků', ceil($consumed / 0.08) * $prices->var('SNECEK'));
        }

        // Sešití více pruhů, když je dodaný materiál užší než výška závěsu + 10 cm.
        // Nulová šířka = materiál nezadán, sešití se neúčtuje.
        if ($parameters->materialWidth > 0 && $parameters->materialWidth < $parameters->height + 10) {
            $joins = floor(($consumed * 100) / ($parameters->materialWidth + 25));
            $lines[] = new PriceLine('Sešití dílů', $joins * $heightMeters * $prices->var('SESITI_DILU'));
        }

        if ($parameters->folding > 0) {
            $lines[] = new PriceLine(
                'Zalomení',
                $parameters->folding * ($parameters->width / 100) * $prices->var('NADSITI_ZACLONA'),
            );
        }

        if ($kanyr = $this->kanyrLine($parameters->kanyr, $parameters->kanyrHeight, $parameters->width, $prices)) {
            $lines[] = $kanyr;
        }

        return PriceResult::fromLines($lines, consumedMeters: $consumed);
    }
}
