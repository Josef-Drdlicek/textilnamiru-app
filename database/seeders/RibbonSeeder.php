<?php

namespace Database\Seeders;

use App\Models\Ribbon;
use Illuminate\Database\Seeder;

/**
 * Řasící stuhy — v původní aplikaci zadrátované v JS, zde v DB dle zadání §8.
 */
class RibbonSeeder extends Seeder
{
    private const array RIBBONS = [
        ['code' => 'custom', 'name' => 'Vlastní', 'price_per_meter' => 0, 'allowed_ratios' => [], 'allows_custom_ratio' => true],
        ['code' => 'siroky70', 'name' => 'Široký sklad 70mm', 'price_per_meter' => 51, 'allowed_ratios' => [1.5, 1.85, 2, 2.5], 'allows_custom_ratio' => false],
        ['code' => 'tuzky85', 'name' => 'Tužky 85mm', 'price_per_meter' => 63, 'allowed_ratios' => [1.5, 2, 2.5, 3], 'allows_custom_ratio' => false],
        ['code' => 'tuzky100', 'name' => 'Tužky 100mm', 'price_per_meter' => 66, 'allowed_ratios' => [2, 2.5], 'allows_custom_ratio' => false],
        ['code' => 'vlna8mm', 'name' => 'Vlna před kolejnic 8mm', 'price_per_meter' => 140, 'allowed_ratios' => [1.5, 2, 2.2], 'allows_custom_ratio' => false],
        ['code' => 'natyc', 'name' => 'Stuha na tyč', 'price_per_meter' => 58, 'allowed_ratios' => [1], 'allows_custom_ratio' => false],
        ['code' => 'double', 'name' => 'Dvojsklad 70mm', 'price_per_meter' => 52, 'allowed_ratios' => [2], 'allows_custom_ratio' => false],
        ['code' => 'triple', 'name' => 'Trojsklad', 'price_per_meter' => 52, 'allowed_ratios' => [2], 'allows_custom_ratio' => false],
    ];

    public function run(): void
    {
        foreach (self::RIBBONS as $index => $ribbon) {
            Ribbon::updateOrCreate(
                ['code' => $ribbon['code']],
                [
                    'name' => $ribbon['name'],
                    'price_per_meter' => $ribbon['price_per_meter'],
                    'allowed_ratios' => $ribbon['allowed_ratios'],
                    'allows_custom_ratio' => $ribbon['allows_custom_ratio'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
