<?php

namespace Database\Seeders;

use App\Models\BlindPriceBand;
use Illuminate\Database\Seeder;

/**
 * Cenová tabulka římských rolet (pásma šířka × výška) — zadání §7.5.
 * V původní aplikaci zadrátovaná v JS, zde v DB editovatelná adminem.
 */
class BlindPriceBandSeeder extends Seeder
{
    private const array WIDTHS = [80, 120, 160, 200, 250, 300];

    private const array HEIGHTS = [100, 150, 200, 250, 300];

    /**
     * Řádky = výšková pásma (v pořadí HEIGHTS), sloupce = šířková pásma (v pořadí WIDTHS).
     */
    private const array PRICES = [
        [552, 672, 756, 852, 972, 1068],
        [672, 756, 852, 972, 1068, 1188],
        [756, 852, 972, 1068, 1188, 1308],
        [852, 972, 1068, 1188, 1308, 1428],
        [948, 1068, 1188, 1308, 1428, 1658],
    ];

    public function run(): void
    {
        foreach (self::HEIGHTS as $row => $maxHeight) {
            foreach (self::WIDTHS as $column => $maxWidth) {
                BlindPriceBand::updateOrCreate(
                    ['max_width' => $maxWidth, 'max_height' => $maxHeight],
                    ['price' => self::PRICES[$row][$column]],
                );
            }
        }
    }
}
