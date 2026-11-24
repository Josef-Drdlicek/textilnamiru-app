<?php

namespace App\Enums;

/**
 * Kolekce látek dodavatele Pavon. Každé provedení rolety se šije z určitých kolekcí
 * (ShadeVariant::fabric_collections), např. roletožaluzie Magic jen z Magico.
 */
enum ShadeFabricCollection: string
{
    case Standard = 'standard';
    case Screen = 'screen';
    case Magico = 'magico';
    case Roma = 'roma';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Screen => 'Screen',
            self::Magico => 'Magico (den a noc)',
            self::Roma => 'Roma',
        };
    }

    /**
     * Stránka kolekce v katalogu textilií Pavon (vše na jedné stránce).
     */
    public function supplierPath(): string
    {
        return '/textilie-'.$this->value.'?ajaxPageOffset=200';
    }
}
