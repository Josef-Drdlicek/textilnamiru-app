<?php

namespace Tests\Support;

use App\Domain\Pricing\PriceList;

/**
 * In-memory ceník s hodnotami přesně dle zadání §8 — golden testy kalkulátorů
 * počítají proti němu, bez databáze.
 */
final class PriceLists
{
    public static function standard(): PriceList
    {
        return new PriceList(
            variables: [
                'VAT' => 21,
                'PRIPRAVA' => 33,
                'OBNITKOVANI' => 7,
                'SIKMINA' => 50,
                'STUHA' => 50,
                'RASICI_STUHA' => 47,
                'OLUVKO' => 50,
                'VSITI_OLUVKO' => 42,
                'NADSITI_ZACLONA' => 45,
                'LEMOVANI' => 47,
                'SESITI_DILU' => 47,
                'USITI_ZALOZKA' => 47,
                'OLUVKO_13G' => 22,
                'OLUVKO_50G' => 67,
                'KORDOVANI' => 10,
                'SNECEK' => 3,
                'RASENI' => 22,
                'ZEHLENI' => 20,
                'USITI_POLSTAR' => 93,
                'USITI_POLSTAR_C' => 175,
                'USITI_SEDAK' => 105,
                'USITI_UVAZ' => 120,
                'MOLITAN_2CM' => 225,
                'ROUNO_025CM2' => 38,
                'VLAKNO_40X40CM' => 130,
                'VLAKNO_50X50CM' => 195,
                'KOMPLET_ROLETA' => 300,
                'POLSTAR_10CM' => 10,
                'POLSTAR_ZAKLAD' => 10,
                'POLSTAR_VYPLN_10CM' => 33,
                'POLSTAR_VYPLN_MIN' => 80,
                'POLSTAR_VYPLN_MIN_CENA' => 130,
                'TUNYLKY_ROLETA' => 200,
            ],
            ribbonPrices: [
                'custom' => 0,
                'siroky70' => 51,
                'tuzky85' => 63,
                'tuzky100' => 66,
                'vlna8mm' => 140,
                'natyc' => 58,
                'double' => 52,
                'triple' => 52,
            ],
            blindBands: self::blindBands(),
        );
    }

    /**
     * @return list<array{max_width: int, max_height: int, price: float}>
     */
    private static function blindBands(): array
    {
        $widths = [80, 120, 160, 200, 250, 300];
        $heights = [100, 150, 200, 250, 300];
        $prices = [
            [552, 672, 756, 852, 972, 1068],
            [672, 756, 852, 972, 1068, 1188],
            [756, 852, 972, 1068, 1188, 1308],
            [852, 972, 1068, 1188, 1308, 1428],
            [948, 1068, 1188, 1308, 1428, 1658],
        ];

        $bands = [];

        foreach ($heights as $row => $maxHeight) {
            foreach ($widths as $column => $maxWidth) {
                $bands[] = [
                    'max_width' => $maxWidth,
                    'max_height' => $maxHeight,
                    'price' => (float) $prices[$row][$column],
                ];
            }
        }

        usort($bands, fn (array $a, array $b) => [$a['max_width'], $a['max_height']] <=> [$b['max_width'], $b['max_height']]);

        return $bands;
    }
}
