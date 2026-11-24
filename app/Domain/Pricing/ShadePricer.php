<?php

namespace App\Domain\Pricing;

use App\Enums\ShadePriceCategory;
use App\Models\ShadeFabric;
use App\Models\ShadePrice;
use App\Models\ShadeVariant;

/**
 * Cena rolety z cenové tabulky provedení: šířka × výška pro cenovou skupinu
 * látky A–D (Kč bez DPH).
 *
 * Cena platí „do“ rozměru: šířka i výška se zaokrouhlí nahoru na nejbližší řádek
 * a sloupec tabulky provedení. Prázdné políčko = cena na poptávku (null), jak slibuje
 * administrace. Ceny mimo aktuální mřížku (po změně kroku či rozsahu) se ignorují.
 */
final readonly class ShadePricer
{
    public function __construct(private PriceList $prices) {}

    /**
     * Null = rozměr mimo rozsah provedení, látka na takovou šířku nestačí,
     * nebo pro rozměr ještě není vyplněná cena.
     */
    public function price(ShadeVariant $variant, ShadeFabric $fabric, float $width, float $height): ?PriceResult
    {
        if (! $fabric->fitsWidth($width)) {
            return null;
        }

        $cell = $this->cell($variant, $fabric->price_category, $width, $height);

        if ($cell === null) {
            return null;
        }

        return PriceResult::fromLines([
            new PriceLine(sprintf(
                'Roleta %s, látka %s – skupina %s (ceník do %d × %d cm)',
                $variant->name,
                $fabric->name,
                $fabric->price_category->value,
                $cell->width,
                $cell->height,
            ), (float) $cell->price),
        ]);
    }

    /**
     * Nejnižší cena provedení bez DPH („od X Kč“); null = ceník je prázdný.
     */
    public function lowestPrice(ShadeVariant $variant): ?float
    {
        $price = $variant->prices()
            ->whereIn('width', $variant->widths())
            ->whereIn('height', $variant->heights())
            ->min('price');

        return $price !== null ? (float) $price : null;
    }

    public function vatRate(): float
    {
        return $this->prices->vatRate();
    }

    public function withVat(float $price): float
    {
        return $this->prices->withVat($price);
    }

    private function cell(ShadeVariant $variant, ShadePriceCategory $category, float $width, float $height): ?ShadePrice
    {
        if (! $variant->fits($width, $height)) {
            return null;
        }

        $gridWidth = $this->roundUpToGrid($variant->widths(), $width);
        $gridHeight = $this->roundUpToGrid($variant->heights(), $height);

        if ($gridWidth === null || $gridHeight === null) {
            return null;
        }

        return $variant->prices()
            ->where('price_category', $category)
            ->where('width', $gridWidth)
            ->where('height', $gridHeight)
            ->first();
    }

    /**
     * @param  list<int>  $axis  vzestupně seřazené hodnoty řádků či sloupců tabulky
     */
    private function roundUpToGrid(array $axis, float $value): ?int
    {
        foreach ($axis as $gridValue) {
            if ($gridValue >= $value) {
                return $gridValue;
            }
        }

        return null;
    }
}
