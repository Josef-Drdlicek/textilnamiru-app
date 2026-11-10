<?php

namespace App\Enums;

/**
 * Rozsah podsvícení translucentního stropu ve veřejné kalkulačce
 * (Specifikace → Světlo). Do ceny zatím nevstupuje.
 */
enum CeilingLighting: string
{
    case Perimeter = 'perimeter';
    case Full = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Perimeter => 'Světlo po obvodu',
            self::Full => 'Světlo přes celý strop',
        };
    }
}
