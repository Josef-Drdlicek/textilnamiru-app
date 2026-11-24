<?php

namespace App\Domain\Suppliers\Pavon;

use App\Enums\ShadeFabricCollection;

/**
 * Rolety Pavon, které nabízíme: produkt => varianty (název => stránka u dodavatele).
 * Pořadí odpovídá pořadí na našem webu. Další roletu přidáte sem a spustíte import.
 */
final class PavonCatalog
{
    public const string BASE_URL = 'https://www.pavon.cz';

    /** @var array<string, array<string, string>> */
    public const array PRODUCTS = [
        'Roleta Leontina' => [
            'Leontina' => '/rolety/retizkova-roleta-leontina',
            'Leontina Box (v kazetě)' => '/rolety/roleta-leontina-box',
        ],
        'Roleta Maestra' => [
            'Maestra' => '/rolety/maestra',
            'Maestra Profil (nalepovací)' => '/rolety/roleta-maestra-profil',
        ],
        'Roleta Bruno' => [
            'Bruno' => '/rolety/roleta-bruno',
        ],
        'Roleta Espiral' => [
            'Espiral' => '/rolety/espiral',
        ],
        'Roleta Rolsun' => [
            'Rolsun' => '/rolety/rolsun',
        ],
        'Roleta Roma' => [
            'Roma' => '/rolety/roma',
        ],
        'Roleta Maestra Magic' => [
            'Maestra Magic' => '/roletozaluzie/maestra-magic',
            'Maestra Magic Profil (nalepovací)' => '/roletozaluzie/roleta-maestra-magic-profil',
            'Magic Open (bez kazety)' => '/roletozaluzie/magic-open',
            'Magic Box (v kazetě)' => '/roletozaluzie/magic-box',
        ],
    ];

    /**
     * Z jakých kolekcí látek se produkt šije (výchozí nastavení provedení).
     *
     * @return list<string>
     */
    public static function fabricCollections(string $productName): array
    {
        return match ($productName) {
            'Roleta Maestra Magic' => [ShadeFabricCollection::Magico->value],
            'Roleta Roma' => [ShadeFabricCollection::Roma->value],
            default => [ShadeFabricCollection::Standard->value, ShadeFabricCollection::Screen->value],
        };
    }
}
