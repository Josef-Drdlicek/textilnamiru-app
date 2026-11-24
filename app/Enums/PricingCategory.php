<?php

namespace App\Enums;

/**
 * Do kterého ceníku v administraci řádek patří. Kódy jsou unikátní napříč
 * všemi ceníky, kalkulace (PriceList) kategorii nerozlišují.
 */
enum PricingCategory: string
{
    case Sewing = 'sewing';
    case Ceiling = 'ceiling';

    /**
     * Stránka administrace, na které se ceník upravuje.
     */
    public function adminRoute(): string
    {
        return match ($this) {
            self::Sewing => 'admin.pricing.index',
            self::Ceiling => 'admin.ceiling-pricing.index',
        };
    }
}
