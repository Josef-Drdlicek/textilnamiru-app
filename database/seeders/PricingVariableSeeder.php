<?php

namespace Database\Seeders;

use App\Models\PricingVariable;
use Illuminate\Database\Seeder;

/**
 * Systémové položky ceníku — výchozí hodnoty dle zadání §8, doplněné
 * majitelem dílny o vypínaný podhled, LED pásek a dopravu.
 *
 * Idempotentní a šetrný k úpravám dílny: chybějící položky založí
 * s výchozí cenou, u existujících mění jen název, jednotku a příznak
 * systémové položky, nikdy hodnotu.
 */
class PricingVariableSeeder extends Seeder
{
    private const array VARIABLES = [
        ['code' => 'VAT', 'name' => 'DPH', 'value' => 21, 'unit' => '%'],
        ['code' => 'PRIPRAVA', 'name' => 'Příprava', 'value' => 33],
        ['code' => 'OBNITKOVANI', 'name' => 'Obnitkování vrchu', 'value' => 7],
        ['code' => 'SIKMINA', 'name' => 'Stříhání šikmina, oblouk', 'value' => 50],
        ['code' => 'STUHA', 'name' => 'Stuha', 'value' => 50],
        ['code' => 'RASICI_STUHA', 'name' => 'Našití řasící stuhy', 'value' => 47],
        ['code' => 'OLUVKO', 'name' => 'Olůvko', 'value' => 50],
        ['code' => 'VSITI_OLUVKO', 'name' => 'Všití oluvka', 'value' => 42],
        ['code' => 'NADSITI_ZACLONA', 'name' => 'Nadšití záclony', 'value' => 45],
        ['code' => 'LEMOVANI', 'name' => 'Lemování', 'value' => 47],
        ['code' => 'SESITI_DILU', 'name' => 'Sešití dílů', 'value' => 47],
        ['code' => 'USITI_ZALOZKA', 'name' => 'Ušití záložky / tunýlku', 'value' => 47],
        ['code' => 'OLUVKO_13G', 'name' => 'Rohové olůvko 13g', 'value' => 22],
        ['code' => 'OLUVKO_50G', 'name' => 'Rohové olůvko 50g', 'value' => 67],
        ['code' => 'KORDOVANI', 'name' => 'Kordovací destička', 'value' => 10],
        ['code' => 'SNECEK', 'name' => 'Šneček', 'value' => 3],
        ['code' => 'RASENI', 'name' => 'Řasení', 'value' => 22],
        ['code' => 'ZEHLENI', 'name' => 'Žehlení, srážení', 'value' => 20],
        ['code' => 'USITI_POLSTAR', 'name' => 'Ušití polštářku', 'value' => 93],
        ['code' => 'USITI_POLSTAR_C', 'name' => 'Ušití polštářku složitý', 'value' => 175],
        ['code' => 'USITI_SEDAK', 'name' => 'Ušití sedáku', 'value' => 105],
        ['code' => 'USITI_UVAZ', 'name' => 'Ušití úvazu', 'value' => 120],
        ['code' => 'MOLITAN_2CM', 'name' => 'Molitan výška 2cm', 'value' => 225],
        ['code' => 'ROUNO_025CM2', 'name' => 'Rouno do sedáku 0,25cm2', 'value' => 38],
        ['code' => 'VLAKNO_40X40CM', 'name' => 'Výplň duté vlákno 40×40cm', 'value' => 130],
        ['code' => 'VLAKNO_50X50CM', 'name' => 'Výplň duté vlákno 50×50cm', 'value' => 195],
        ['code' => 'KOMPLET_ROLETA', 'name' => 'Kompletace římské rolety', 'value' => 300],
        ['code' => 'POLSTAR_10CM', 'name' => 'Polštář rozměr po 10cm', 'value' => 10],
        ['code' => 'POLSTAR_ZAKLAD', 'name' => 'Polštář základ ceny', 'value' => 10],
        ['code' => 'POLSTAR_VYPLN_10CM', 'name' => 'Polštář výplň po 10cm', 'value' => 33],
        ['code' => 'POLSTAR_VYPLN_MIN', 'name' => 'Polštář výplň minimum rozměr', 'value' => 80],
        ['code' => 'POLSTAR_VYPLN_MIN_CENA', 'name' => 'Polštář výplň minimum cena', 'value' => 130],
        ['code' => 'TUNYLKY_ROLETA', 'name' => 'Odšité tunýlky římské rolety', 'value' => 200],
        ['code' => 'VYPINANY_PODHLED', 'name' => 'Vypínaný podhled', 'value' => 1500, 'unit' => 'm²'],
        ['code' => 'LED_PASEK', 'name' => 'LED pásek', 'value' => 400, 'unit' => 'm'],
        ['code' => 'DOPRAVA', 'name' => 'Doprava', 'value' => 12, 'unit' => 'km'],
    ];

    public function run(): void
    {
        foreach (self::VARIABLES as $default) {
            $variable = PricingVariable::firstOrNew(['code' => $default['code']]);

            $variable->fill([
                'name' => $default['name'],
                'unit' => $default['unit'] ?? null,
                'is_system' => true,
            ]);

            if (! $variable->exists) {
                $variable->value = $default['value'];
            }

            $variable->save();
        }
    }
}
