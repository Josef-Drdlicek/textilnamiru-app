<?php

namespace App\Enums;

/**
 * Druh výrobku v Zavěšení textilu.
 */
enum RailKind: string
{
    case Rail = 'rail';
    case Decorative = 'decorative';

    public function label(): string
    {
        return match ($this) {
            self::Rail => 'Kolejnice',
            self::Decorative => 'Dekorační garnýže',
        };
    }
}
