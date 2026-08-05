<?php

namespace App\Domain\Pricing;

/**
 * Jedna řádka rozpadu ceny („z čeho se cena skládá").
 */
final readonly class PriceLine
{
    public function __construct(
        public string $label,
        public float $amount, // Kč bez DPH, nezaokrouhleno
    ) {}

    /**
     * @return array{label: string, amount: float}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'amount' => round($this->amount, 2),
        ];
    }
}
