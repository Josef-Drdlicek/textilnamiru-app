<?php

namespace App\Enums;

/**
 * Produktové kategorie nabízené na veřejném webu. Širší množina než ItemType —
 * garnýže, žaluzie a japonské stěny aplikace neumí nacenit (jsou „na poptávku"),
 * ale zákazník o ně může projevit zájem.
 */
enum InquiryProduct: string
{
    case Curtains = 'curtains';
    case Drapes = 'drapes';
    case Pillows = 'pillows';
    case Cushions = 'cushions';
    case Blinds = 'blinds';
    case StretchCeilings = 'stretch_ceilings';
    case Rails = 'rails';
    case Shades = 'shades';
    case Panels = 'panels';

    public function label(): string
    {
        return match ($this) {
            self::Curtains => 'Záclony',
            self::Drapes => 'Závěsy',
            self::Pillows => 'Polštáře',
            self::Cushions => 'Sedáky',
            self::Blinds => 'Římské rolety',
            self::StretchCeilings => 'Vypínané stropy',
            self::Rails => 'Garnýže a kolejnice',
            self::Shades => 'Žaluzie a stínění',
            self::Panels => 'Japonské stěny',
        };
    }

    /**
     * Odpovídající kalkulovatelný typ položky, pokud existuje.
     */
    public function itemType(): ?ItemType
    {
        return match ($this) {
            self::Curtains => ItemType::Curtain,
            self::Drapes => ItemType::Drape,
            self::Pillows => ItemType::Pillow,
            self::Cushions => ItemType::Cushion,
            self::Blinds => ItemType::Blind,
            self::StretchCeilings => ItemType::StretchCeiling,
            self::Rails, self::Shades, self::Panels => null,
        };
    }

    public static function fromItemType(ItemType $type): self
    {
        return match ($type) {
            ItemType::Curtain => self::Curtains,
            ItemType::Drape => self::Drapes,
            ItemType::Pillow => self::Pillows,
            ItemType::Cushion => self::Cushions,
            ItemType::Blind => self::Blinds,
            ItemType::StretchCeiling => self::StretchCeilings,
        };
    }
}
