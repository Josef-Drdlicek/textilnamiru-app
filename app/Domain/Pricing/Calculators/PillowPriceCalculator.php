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
 * Polštář — vzorec dle zadání §7.3, doplněný majitelem dílny o ušití
 * (běžné / složitý tvar) a příplatek za zip.
 */
class PillowPriceCalculator implements PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult
    {
        if (! $parameters instanceof PillowParameters) {
            throw new InvalidArgumentException('Kalkulátor polštáře vyžaduje PillowParameters.');
        }

        // Součet rozměrů zaokrouhlený po 10 cm nahoru.
        $size = ceil($parameters->width / 10) + ceil($parameters->height / 10);

        $lines = [
            new PriceLine('Základ ceny', $prices->var('POLSTAR_ZAKLAD')),
            new PriceLine('Cena dle rozměru', $size * $prices->var('POLSTAR_10CM')),
            $parameters->complex
                ? new PriceLine('Ušití polštáře (složitý tvar)', $prices->var('USITI_POLSTAR_C'))
                : new PriceLine('Ušití polštáře', $prices->var('USITI_POLSTAR')),
        ];

        if ($parameters->stuffing) {
            $lines[] = new PriceLine('Výplň — základ', $prices->var('POLSTAR_VYPLN_MIN_CENA'));

            $minSize = $prices->var('POLSTAR_VYPLN_MIN');

            if ($size * 10 > $minSize) {
                $lines[] = new PriceLine(
                    'Výplň nad minimum',
                    ceil(($size * 10 - $minSize) / 10) * $prices->var('POLSTAR_VYPLN_10CM'),
                );
            }
        }

        if ($parameters->zipper->hasZipper()) {
            $lines[] = new PriceLine('Zip', $prices->var('USITI_ZALOZKA'));
        }

        return PriceResult::fromLines($lines);
    }
}
