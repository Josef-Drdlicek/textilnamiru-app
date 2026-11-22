<?php

namespace App\Enums;

/**
 * Kategorie látek v ceníku textilu a ve veřejném katalogu záclon a závěsů.
 */
enum TextileCategory: string
{
    case Curtain = 'curtain';
    case Drape = 'drape';

    public function label(): string
    {
        return match ($this) {
            self::Curtain => 'Záclona',
            self::Drape => 'Závěs',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Curtain => 'Záclony',
            self::Drape => 'Závěsy',
        };
    }

    /**
     * Typ položky zakázky/poptávky, kterým se výrobek z látky šije.
     */
    public function itemType(): ItemType
    {
        return match ($this) {
            self::Curtain => ItemType::Curtain,
            self::Drape => ItemType::Drape,
        };
    }

    /**
     * Co znamená rozměr role u dodavatele: záclona se šije po výšce role,
     * závěs z dekorační látky podle šíře (u úzkých látek ze svislých pruhů).
     */
    public function rollLabel(): string
    {
        return match ($this) {
            self::Curtain => 'Výška',
            self::Drape => 'Šíře látky',
        };
    }

    /**
     * Veřejná produktová stránka látky.
     */
    public function showRoute(): string
    {
        return match ($this) {
            self::Curtain => 'web.zaclony.show',
            self::Drape => 'web.zavesy.show',
        };
    }

    /**
     * Veřejný seznam látek kategorie.
     */
    public function indexRoute(): string
    {
        return match ($this) {
            self::Curtain => 'web.zaclony',
            self::Drape => 'web.zavesy',
        };
    }
}
