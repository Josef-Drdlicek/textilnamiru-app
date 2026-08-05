<?php

namespace App\Domain\Pricing\Calculators;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Domain\Pricing\Parameters\PillowParameters;
use App\Domain\Pricing\PriceCalculator;
use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;
use App\Domain\Pricing\PriceResult;
use InvalidArgumentException;

/**
 * Polštář — vzorec dle zadání §7.3. `zipper` cenu neovlivňuje.
 */
class PillowPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof PillowParameters) {
            throw new InvalidArgumentException('Kalkulátor polštáře vyžaduje PillowParameters.');
        }

        return PriceResult::fromLines(
            $this->buildLines($parameters->width, $parameters->height, $parameters->stuffing, $prices),
        );
    }

    /**
     * Sdíleno se sedákem ({@see CushionPriceCalculator}), který se v původní
     * aplikaci počítá identicky jako polštář.
     *
     * @return list<PriceLine>
     */
    public function buildLines(float $width, float $height, bool $stuffing, PriceList $prices): array
    {
        // Součet rozměrů zaokrouhlený po 10 cm nahoru.
        $size = ceil($width / 10) + ceil($height / 10);

        $lines = [
            new PriceLine('Základ ceny', $prices->var('POLSTAR_ZAKLAD')),
            new PriceLine('Cena dle rozměru', $size * $prices->var('POLSTAR_10CM')),
        ];

        if ($stuffing) {
            $lines[] = new PriceLine('Výplň — základ', $prices->var('POLSTAR_VYPLN_MIN_CENA'));

            $minSize = $prices->var('POLSTAR_VYPLN_MIN');

            if ($size * 10 > $minSize) {
                $lines[] = new PriceLine(
                    'Výplň nad minimum',
                    ceil(($size * 10 - $minSize) / 10) * $prices->var('POLSTAR_VYPLN_10CM'),
                );
            }
        }

        return $lines;
    }
}
