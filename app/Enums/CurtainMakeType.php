<?php

namespace App\Enums;

/**
 * Typ výrobku na produktové stránce látky (krok „Typ výrobku“).
 * Online cenu zatím počítáme jen pro záclonu s řasící páskou, ostatní na poptávku.
 */
enum CurtainMakeType: string
{
    case Tape = 'tape';
    case Rings = 'rings';
    case FixedPleats = 'fixed_pleats';

    public function label(TextileCategory $category = TextileCategory::Curtain): string
    {
        return match ($this) {
            self::Tape => "{$category->label()} s řasící páskou",
            self::Rings => "{$category->label()} s kroužky",
            self::FixedPleats => 'Zavěšení s pevnými sklady',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Tape => 'Nejčastější řešení. Páska nahoře látku nařasí do pravidelných skladů, věší se na háčky do kolejnice.',
            self::Rings => 'Do horního okraje zalisujeme kovové nebo plastové kroužky a látka se navlékne na tyč.',
            self::FixedPleats => 'Sklady jsou pevně prošité, takže látka drží stále stejný tvar.',
        };
    }

    public function isPricedOnline(): bool
    {
        return $this === self::Tape;
    }
}
