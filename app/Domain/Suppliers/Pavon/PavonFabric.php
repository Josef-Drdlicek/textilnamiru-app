<?php

namespace App\Domain\Suppliers\Pavon;

use App\Enums\ShadePriceCategory;

/**
 * Jedna látka z katalogu textilií Pavon.
 */
final readonly class PavonFabric
{
    /**
     * @param  list<array{label: string, value: string}>  $specs
     */
    public function __construct(
        public string $code,
        public string $name,
        public ?ShadePriceCategory $priceCategory,
        public bool $onSale,
        public ?int $rollWidth,
        public array $specs,
        public ?string $imageUrl,
    ) {}
}
