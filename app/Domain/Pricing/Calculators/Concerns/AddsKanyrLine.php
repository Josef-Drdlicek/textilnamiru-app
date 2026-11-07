<?php

namespace App\Domain\Pricing\Calculators\Concerns;

use App\Domain\Pricing\PriceLine;
use App\Domain\Pricing\PriceList;

/**
 * Kanýr u záclon a závěsů: plocha kanýru [m²] × sazba obnitkování.
 * Vzorec stanovil majitel dílny (Lucien), sazba je v ceníku jako OBNITKOVANI.
 */
trait AddsKanyrLine
{
    private function kanyrLine(bool $kanyr, ?float $kanyrHeight, float $width, PriceList $prices): ?PriceLine
    {
        if (! $kanyr || ! $kanyrHeight) {
            return null;
        }

        return new PriceLine('Kanýr', ($width / 100) * ($kanyrHeight / 100) * $prices->var('OBNITKOVANI'));
    }
}
