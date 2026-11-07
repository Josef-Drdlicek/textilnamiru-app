<?php

namespace App\Enums;

/**
 * Povrch fólie vypínaného stropu. Do ceny zatím nevstupuje
 * (ceník má jednu sazbu VYPINANY_PODHLED za m²).
 */
enum CeilingSurface: string
{
    case Matte = 'matte';
    case Glossy = 'glossy';
    case Satin = 'satin';
    case Backlit = 'backlit';

    public function label(): string
    {
        return match ($this) {
            self::Matte => 'Matný',
            self::Glossy => 'Lesklý',
            self::Satin => 'Saténový',
            self::Backlit => 'Podsvícený',
        };
    }
}
