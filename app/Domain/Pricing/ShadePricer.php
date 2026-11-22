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
 * Cena platí „do“ rozměru: zadaný rozměr se zaokrouhlí nahoru na nejbližší
 * vyplněné políčko tabulky dané skupiny. Doplňkový sortiment přibude jako další řádky.
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
        $price = $variant->prices()->min('price');

        return $price !== null ? (float) $price : null;
    }

    public function vatRate(): float
    {
        return $this->prices->vatRate();
    }

    public function withVat(float $price): float
    {
        return round($price * (100 + $this->vatRate())) / 100;
    }

    private function cell(ShadeVariant $variant, ShadePriceCategory $category, float $width, float $height): ?ShadePrice
    {
        if (! $variant->fits($width, $height)) {
            return null;
        }

        return $variant->prices()
            ->where('price_category', $category)
            ->where('width', '>=', $width)
            ->where('height', '>=', $height)
            ->orderBy('width')
            ->orderBy('height')
            ->first();
    }
}
