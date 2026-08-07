<?php

namespace App\Enums;

/**
 * Typ prostoru, pro který zákazník poptává textil.
 */
enum SpaceType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Office = 'office';
    case Commercial = 'commercial';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Apartment => 'Byt',
            self::House => 'Rodinný dům',
            self::Office => 'Kancelář / firma',
            self::Commercial => 'Hotel / společenský prostor',
            self::Other => 'Jiné',
        };
    }
}
