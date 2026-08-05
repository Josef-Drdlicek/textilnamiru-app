<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\Parameters\ItemParameters;

/**
 * Strategie výpočtu ceny pro jeden typ výrobku.
 *
 * Kalkulátory jsou bezstavové a nesahají do databáze — všechny ceníkové
 * hodnoty dostávají přes {@see PriceList}. Vzorce odpovídají zadání §7.
 */
interface PriceCalculator
{
    public function calculate(ItemParameters $parameters, PriceList $prices): PriceResult;
}
