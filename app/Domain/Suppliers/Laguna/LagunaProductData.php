<?php

namespace App\Domain\Suppliers\Laguna;

use App\Enums\TextileCategory;

/**
 * Látka připravená k uložení: název + výška = jeden produkt, barvy s fotkami.
 */
final readonly class LagunaProductData
{
    /**
     * @param  array<string, string>  $colors  kód barvy => URL fotky
     * @param  list<array{0: string, 1: string}>  $specs  [[popis, hodnota], …]
     */
    public function __construct(
        public string $code,          // 11101/180 nebo 1080/315 FR — jedinečný klíč produktu u dodavatele
        public string $baseName,      // 11101 nebo 1080 FR
        public int $height,           // výška (záclona) nebo šíře (dekorační látka) role [cm]
        public array $colors,
        public ?string $material,
        public array $specs,
        public TextileCategory $category = TextileCategory::Curtain,
    ) {}

    public function name(): string
    {
        return "{$this->category->label()} {$this->baseName} – {$this->height} cm";
    }
}
