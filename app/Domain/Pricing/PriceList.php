<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\Exceptions\UnknownPricingVariable;
use App\Domain\Pricing\Exceptions\UnknownRibbon;
use App\Models\PricingVariable;
use App\Models\Ribbon;

/**
 * Snapshot ceníku pro jednu kalkulaci — jediný zdroj cenových hodnot
 * pro kalkulátory. Načítá se jednou (scoped binding v kontejneru),
 * kalkulátory tak do databáze nesahají a jdou testovat čistě v paměti.
 */
final readonly class PriceList
{
    /**
     * @param  array<string, float>  $variables  kód ceníkové proměnné => hodnota [Kč]
     * @param  array<string, float>  $ribbonPrices  kód stuhy => cena za metr [Kč]
     */
    public function __construct(
        private array $variables,
        private array $ribbonPrices,
    ) {}

    public static function fromDatabase(): self
    {
        return new self(
            variables: PricingVariable::query()->pluck('value', 'code')->map(floatval(...))->all(),
            ribbonPrices: Ribbon::query()->pluck('price_per_meter', 'code')->map(floatval(...))->all(),
        );
    }

    public function var(string $code): float
    {
        return $this->variables[$code] ?? throw UnknownPricingVariable::forCode($code);
    }

    public function vatRate(): float
    {
        return $this->var('VAT');
    }

    public function ribbonPricePerMeter(string $code): float
    {
        return $this->ribbonPrices[$code] ?? throw UnknownRibbon::forCode($code);
    }
}
