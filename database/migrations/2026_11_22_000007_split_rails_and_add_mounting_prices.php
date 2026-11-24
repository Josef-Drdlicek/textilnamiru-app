<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Každý typ kolejnice (N1 Napoli, T1 Torino…) je samostatný produkt s vlastní cenou,
 * jeho varianty jsou sestavy (T1-11 Jednokolejnička rovná…). Cena se navíc liší
 * podle způsobu zavěšení: stropní nebo stěnový držák. Stávající ceny = stropní.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rail_products', function (Blueprint $table) {
            $table->string('line', 30)->nullable()->after('kind');     // řada dodavatele, např. „N-Line“
            $table->string('code', 20)->nullable()->after('name');     // např. „N1“
            $table->text('description')->nullable()->after('subtitle');
            $table->json('mountings')->nullable()->after('components'); // RailMounting: ceiling, wall
        });

        // Nový unikátní index musí vzniknout dřív, než zmizí starý: MySQL o starý index
        // opírá cizí klíč rail_variant_id a bez náhrady by ho odmítl smazat (chyba 1553).
        Schema::table('rail_prices', function (Blueprint $table) {
            $table->string('mounting', 10)->default('ceiling')->after('rail_variant_id');
            $table->unique(['rail_variant_id', 'mounting', 'length_to']);
        });

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->dropUnique(['rail_variant_id', 'length_to']);
        });
    }

    public function down(): void
    {
        // Bez způsobu zavěšení zůstanou jen stropní ceny, stěnové by porušily starý index.
        DB::table('rail_prices')->where('mounting', '!=', 'ceiling')->delete();

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->unique(['rail_variant_id', 'length_to']);
        });

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->dropUnique(['rail_variant_id', 'mounting', 'length_to']);
            $table->dropColumn('mounting');
        });

        Schema::table('rail_products', function (Blueprint $table) {
            $table->dropColumn(['line', 'code', 'description', 'mountings']);
        });
    }
};
