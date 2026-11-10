<?php

namespace Database\Seeders;

use App\Enums\PricingCategory;
use App\Models\PricingVariable;
use Illuminate\Database\Seeder;

/**
 * Položky ceníku stropů zadané majitelem dílny. Ceny zatím nejsou známé,
 * proto startují na 0 Kč a dílna je doplní v administraci.
 *
 * Idempotentní: chybějící položky založí, existující (a jejich ceny) nechá být.
 * Nejsou systémové — žádný vzorec je zatím nepoužívá, dílna je může smazat.
 */
class CeilingPricingVariableSeeder extends Seeder
{
    private const array VARIABLES = [
        ['code' => 'STROP_FOLIE_LESK', 'name' => 'Fólie lesk', 'unit' => 'm²'],
        ['code' => 'STROP_FOLIE_MAT', 'name' => 'Fólie mat', 'unit' => 'm²'],
        ['code' => 'STROP_FOLIE_TRANS', 'name' => 'Fólie trans', 'unit' => 'm²'],
        ['code' => 'STROP_LISTA_STROP', 'name' => 'Lišta strop', 'unit' => 'bm'],
        ['code' => 'STROP_LISTA_STENA', 'name' => 'Lišta stěna', 'unit' => 'bm'],
        ['code' => 'STROP_LISTA_SVETELNA', 'name' => 'Lišta světelná', 'unit' => 'bm'],
        ['code' => 'STROP_LED_COB_15W', 'name' => 'LED COB 15 W', 'unit' => 'bm'],
        ['code' => 'STROP_ZDROJ', 'name' => 'Napájecí zdroj', 'unit' => 'ks'],
        ['code' => 'STROP_ZDROJ_SLIM_100W', 'name' => 'Slim LED napájecí zdroj 100 W / 24 V', 'unit' => 'ks'],
        ['code' => 'STROP_ZDROJ_SLIM_200W', 'name' => 'Slim LED napájecí zdroj 200 W / 24 V', 'unit' => 'ks'],
        ['code' => 'STROP_ZDROJ_SLIM_400W', 'name' => 'Slim LED napájecí zdroj 400 W / 24 V', 'unit' => 'ks'],
        ['code' => 'STROP_PRIJIMAC', 'name' => 'Přijímač', 'unit' => 'ks'],
        ['code' => 'STROP_DALKOVY_OVLADAC', 'name' => 'Dálkový ovladač', 'unit' => 'ks'],
    ];

    public function run(): void
    {
        foreach (self::VARIABLES as $default) {
            PricingVariable::firstOrCreate(['code' => $default['code']], [
                'name' => $default['name'],
                'unit' => $default['unit'],
                'value' => 0,
                'is_system' => false,
                'category' => PricingCategory::Ceiling,
            ]);
        }
    }
}
