<?php

use Database\Seeders\BlindPriceBandSeeder;
use Database\Seeders\PricingVariableSeeder;
use Database\Seeders\RibbonSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Doseje výchozí ceník do prostředí, kde nikdy neproběhl `db:seed`
 * (produkce měla prázdné pricing_variables/ribbons/blind_price_bands
 * a veřejný web bez ceníku nemůže počítat ceny).
 *
 * Seedery používají updateOrCreate — na již naplněné databázi resetují
 * výchozí položky na hodnoty ze seedu, vlastní úpravy dílny nad rámec
 * výchozích kódů nechávají být. Jako migrace běží právě jednou, takže
 * budoucí úpravy ceníku dílnou už žádný deploy nepřepíše.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new PricingVariableSeeder)->run();
        (new RibbonSeeder)->run();
        (new BlindPriceBandSeeder)->run();
    }

    public function down(): void
    {
        // Data zůstávají — výchozí ceník nechceme mazat.
    }
};
