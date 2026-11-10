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
 *
 * Se světelnou lištou (stanovil majitel dílny): po obvodu stropu
 * světelná lišta × STROP_LISTA_SVETELNA a LED COB 15 W × STROP_LED_COB_15W,
 * na každý kus navíc jeden zdroj STROP_ZDROJ_SLIM_400W a jeden STROP_PRIJIMAC.
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

        if ($parameters->lightStrip) {
            array_push($lines, ...$this->lightStripLines($parameters, $prices));
        }

        if ($parameters->ledLength > 0) {
            $lines[] = new PriceLine('LED pásek', $parameters->ledLength * $prices->var('LED_PASEK'));
        }

        if ($parameters->distance > 0) {
            $lines[] = new PriceLine('Doprava', $parameters->distance * $prices->var('DOPRAVA'));
        }

        return PriceResult::fromLines($lines);
    }

    /**
     * @return list<PriceLine>
     */
    private function lightStripLines(StretchCeilingParameters $parameters, PriceList $prices): array
    {
        $perimeter = $parameters->perimeterM();
        $meters = rtrim(rtrim(number_format($perimeter, 2, ',', ' '), '0'), ',');

        return [
            new PriceLine("Lišta světelná ({$meters} bm)", $perimeter * $prices->var('STROP_LISTA_SVETELNA')),
            new PriceLine("LED COB 15 W ({$meters} bm)", $perimeter * $prices->var('STROP_LED_COB_15W')),
            new PriceLine('Napájecí zdroj 400 W', $prices->var('STROP_ZDROJ_SLIM_400W')),
            new PriceLine('Přijímač', $prices->var('STROP_PRIJIMAC')),
        ];
    }
}
