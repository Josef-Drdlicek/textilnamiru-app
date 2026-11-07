<?php

use Database\Seeders\PricingVariableSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ceník dostává jednotky (m², m, km, %…) a rozlišení systémových položek,
 * které používají vzorce, od vlastních řádků dílny. Systémové položky
 * nejdou smazat, jinak by kalkulace přestaly fungovat.
 *
 * Seeder doplní chybějící položky (nově vypínaný podhled, LED pásek,
 * doprava) a u existujících jen metadata — hodnoty nastavené dílnou
 * nepřepisuje.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_variables', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->after('value');
            $table->boolean('is_system')->default(false)->after('unit');
        });

        (new PricingVariableSeeder)->run();
    }

    public function down(): void
    {
        Schema::table('pricing_variables', function (Blueprint $table) {
            $table->dropColumn(['unit', 'is_system']);
        });
    }
};
