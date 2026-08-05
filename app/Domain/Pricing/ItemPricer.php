<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use Illuminate\Contracts\Container\Container;

/**
 * Vstupní bod kalkulace: podle typu položky vybere správný kalkulátor
 * a spočítá cenu nad aktuálním ceníkem.
 */
final readonly class ItemPricer
{
    public function __construct(
        private Container $container,
        private PriceList $priceList,
    ) {}

    public function price(ItemType $type, ItemParameters $parameters): PriceResult
    {
        /** @var PriceCalculator $calculator */
        $calculator = $this->container->make($type->calculatorClass());

        return $calculator->calculate($parameters, $this->priceList);
    }

    public function vatRate(): float
    {
        return $this->priceList->vatRate();
    }
}
