<?php

namespace App\Domain\Suppliers\Laguna;

use App\Enums\TextileCategory;

/**
 * Katalogy Laguna Textil, které importujeme, a kam patří v našem Ceníku textilu.
 */
enum LagunaCatalog: string
{
    case Curtains = 'zaclony';
    case Drapes = 'zavesy';

    /** Stránka kategorie u dodavatele (odtud se bere CSRF token). */
    public function path(): string
    {
        return match ($this) {
            self::Curtains => '/cz/kategorie-produktu/zaclony-1',
            self::Drapes => '/cz/kategorie-produktu/dekoracni-latky',
        };
    }

    /** product_category_id ve výpisu dodavatele. */
    public function categoryId(): string
    {
        return match ($this) {
            self::Curtains => '1',
            self::Drapes => '2',
        };
    }

    public function textileCategory(): TextileCategory
    {
        return match ($this) {
            self::Curtains => TextileCategory::Curtain,
            self::Drapes => TextileCategory::Drape,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Curtains => 'záclony',
            self::Drapes => 'dekorační látky (závěsy)',
        };
    }
}
