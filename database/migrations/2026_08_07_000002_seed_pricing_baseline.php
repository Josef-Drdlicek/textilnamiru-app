<?php

use Database\Seeders\RibbonSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Doseje výchozí ceník do prostředí, kde nikdy neproběhl `db:seed`
 * (produkce měla prázdný ceník a veřejný web bez něj nemůže počítat ceny).
 *
 * Ceníkové proměnné doseje až 2026_11_07_000001, protože seeder potřebuje
 * sloupce přidané tam. Tabulka pásem rolet byla zrušena (2026_11_07_000002).
 */
return new class extends Migration
{
    public function up(): void
    {
        (new RibbonSeeder)->run();
    }

    public function down(): void
    {
        // Data zůstávají — výchozí ceník nechceme mazat.
    }
};
