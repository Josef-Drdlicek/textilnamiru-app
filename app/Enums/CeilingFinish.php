<?php

namespace App\Enums;

/**
 * Druh fólie jednobarevného stropu ve veřejné kalkulačce (Specifikace → Druh).
 * Do ceny zatím nevstupuje; zapisuje se do souhrnu poptávky.
 */
enum CeilingFinish: string
{
    case Glossy = 'glossy';
    case Matte = 'matte';
    case Structure = 'structure';
    case Satin = 'satin';

    public function label(): string
    {
        return match ($this) {
            self::Glossy => 'Lesk',
            self::Matte => 'Mat',
            self::Structure => 'Struktura',
            self::Satin => 'Satin',
        };
    }

    /**
     * Povrch fólie v parametrech výrobku. Strukturovaná fólie zatím nemá
     * vlastní povrch, ukládá se jako matná; druh je vidět v souhrnu poptávky.
     */
    public function surface(): CeilingSurface
    {
        return match ($this) {
            self::Glossy => CeilingSurface::Glossy,
            self::Matte, self::Structure => CeilingSurface::Matte,
            self::Satin => CeilingSurface::Satin,
        };
    }
}
