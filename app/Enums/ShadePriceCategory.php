<?php

namespace App\Enums;

/**
 * Cenová skupina látky na rolety. Každá roleta má pro každou skupinu
 * vlastní cenovou tabulku šířka × výška.
 */
enum ShadePriceCategory: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';

    public function label(): string
    {
        return 'Cenová skupina '.$this->value;
    }

    /**
     * Ze zápisu dodavatele: „A (nejlevnější)“, „A* (levnější skupina A)“, „C“ … → A–D.
     */
    public static function fromSupplier(?string $value): ?self
    {
        return preg_match('/^\s*([A-D])/u', (string) $value, $match) ? self::from($match[1]) : null;
    }
}
