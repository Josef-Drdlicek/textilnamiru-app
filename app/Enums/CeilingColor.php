<?php

namespace App\Enums;

/**
 * Barva fólie jednobarevného stropu ve veřejné kalkulačce (Specifikace → Barva).
 * Zatím čtyři základní barvy, další přidáte sem. Do ceny nevstupuje.
 */
enum CeilingColor: string
{
    case White = 'white';
    case Beige = 'beige';
    case Grey = 'grey';
    case Black = 'black';

    public function label(): string
    {
        return match ($this) {
            self::White => 'Bílá',
            self::Beige => 'Béžová',
            self::Grey => 'Šedá',
            self::Black => 'Černá',
        };
    }
}
