<?php

namespace App\Domain\Pricing;

/**
 * Výsledek serverové kalkulace ceny jedné položky.
 */
final readonly class PriceResult
{
    /**
     * @param  list<PriceLine>  $lines
     */
    public function __construct(
        public float $unitPrice,       // cena za kus bez DPH, zaokrouhlená na 2 des. místa
        public float $consumedMeters,  // spotřeba materiálu za kus [m]; 0 u výrobků bez spotřeby
        public array $lines,
    ) {}

    /**
     * @param  list<PriceLine>  $lines
     */
    public static function fromLines(array $lines, float $consumedMeters = 0.0): self
    {
        $sum = array_sum(array_map(fn (PriceLine $line) => $line->amount, $lines));

        // Replikuje zaokrouhlení původní aplikace: round(price * 100) / 100.
        return new self(round($sum, 2), $consumedMeters, $lines);
    }

    public function unitPriceWithVat(float $vatRate): float
    {
        return round($this->unitPrice * (100 + $vatRate)) / 100;
    }

    public function totalPrice(int $count): float
    {
        return round($this->unitPrice * $count, 2);
    }

    public function totalPriceWithVat(float $vatRate, int $count): float
    {
        return round($this->unitPriceWithVat($vatRate) * $count, 2);
    }

    /**
     * @return list<array{label: string, amount: float}>
     */
    public function linesToArray(): array
    {
        return array_map(fn (PriceLine $line) => $line->toArray(), $this->lines);
    }
}
