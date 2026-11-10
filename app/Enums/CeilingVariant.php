<?php

namespace App\Enums;

/**
 * Provedení vypínaného stropu nabízené ve veřejné kalkulačce.
 * Zatím do ceny nevstupuje (ceník má jednu sazbu VYPINANY_PODHLED za m²);
 * určuje, co se ptá ve Specifikaci, a zapisuje se do souhrnu poptávky.
 */
enum CeilingVariant: string
{
    case Monochrome = 'monochrome';
    case LightStrip = 'light_strip';
    case Translucent = 'translucent';

    public function label(): string
    {
        return match ($this) {
            self::Monochrome => 'Jednobarevný',
            self::LightStrip => 'Jednobarevný se světelnou lištou',
            self::Translucent => 'Translucentní',
        };
    }

    /**
     * Jednobarevné provedení se ve Specifikaci ptá na druh fólie a barvu.
     */
    public function hasFinishAndColor(): bool
    {
        return in_array($this, [self::Monochrome, self::LightStrip], true);
    }

    /**
     * Translucentní provedení se ve Specifikaci ptá na rozsah světla.
     */
    public function hasLighting(): bool
    {
        return $this === self::Translucent;
    }

    /**
     * Základ názvu fotky v public/images/web/ (viz <x-web.img>).
     * Stačí tam nahrát {název}.webp a {název}.jpg, dokud nejsou, ukáže se placeholder.
     */
    public function imageName(): string
    {
        return 'kalkulacka-strop-'.str_replace('_', '-', $this->value);
    }
}
