<?php

namespace App\Domain\Pricing;

use App\Enums\RailMounting;
use App\Models\RailPrice;
use App\Models\RailVariant;

/**
 * Cena kolejnice či garnýže: sestava × způsob zavěšení (stropní / stěnový držák)
 * × délka v pásmech po 50 cm do 6 m (1–50, 51–100 … 551–600 cm).
 * Délka se zaokrouhlí nahoru na pásmo.
 */
final readonly class RailPricer
{
    public const int BAND = 50;

    public const int MAX_LENGTH = 600;

    public function __construct(private PriceList $prices) {}

    /**
     * Horní meze pásem: 50, 100 … 600.
     *
     * @return list<int>
     */
    public static function bands(): array
    {
        return range(self::BAND, self::MAX_LENGTH, self::BAND);
    }

    /**
     * Popisek pásma, např. „51–100 cm“.
     */
    public static function bandLabel(int $lengthTo): string
    {
        return ($lengthTo - self::BAND + 1).'–'.$lengthTo.' cm';
    }

    /**
     * Null = délka mimo 1–600 cm, nebo cena pásma ještě není vyplněná.
     */
    public function price(RailVariant $variant, RailMounting $mounting, float $length): ?PriceResult
    {
        if ($length <= 0 || $length > self::MAX_LENGTH) {
            return null;
        }

        $band = self::bandFor($length);
        /** @var RailPrice|null $price */
        $price = $variant->prices()->where('mounting', $mounting)->where('length_to', $band)->first();

        if ($price === null) {
            return null;
        }

        return PriceResult::fromLines([
            new PriceLine(sprintf(
                '%s – %s, %s (%s)',
                $variant->product->name,
                $variant->name,
                mb_strtolower($mounting->label()),
                self::bandLabel($band),
            ), (float) $price->price),
        ]);
    }

    /**
     * Horní mez pásma pro délku: 120 cm → 150.
     */
    public static function bandFor(float $length): int
    {
        return (int) (ceil($length / self::BAND) * self::BAND);
    }

    /**
     * Nejnižší cena produktu bez DPH („od X Kč“); null = ceník je prázdný.
     *
     * @param  iterable<RailVariant>  $variants
     */
    public function lowestPrice(iterable $variants): ?float
    {
        $ids = collect($variants)->pluck('id');
        $price = RailPrice::query()->whereIn('rail_variant_id', $ids)->min('price');

        return $price !== null ? (float) $price : null;
    }

    public function vatRate(): float
    {
        return $this->prices->vatRate();
    }

    public function withVat(float $price): float
    {
        return $this->prices->withVat($price);
    }
}
