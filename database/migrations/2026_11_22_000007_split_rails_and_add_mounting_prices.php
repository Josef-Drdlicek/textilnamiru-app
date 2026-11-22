<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->dropUnique(['rail_variant_id', 'length_to']);
        });

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->string('mounting', 10)->default('ceiling')->after('rail_variant_id');
            $table->unique(['rail_variant_id', 'mounting', 'length_to']);
        });
    }

    public function down(): void
    {
        Schema::table('rail_prices', function (Blueprint $table) {
            $table->dropUnique(['rail_variant_id', 'mounting', 'length_to']);
        });

        Schema::table('rail_prices', function (Blueprint $table) {
            $table->dropColumn('mounting');
            $table->unique(['rail_variant_id', 'length_to']);
        });

        Schema::table('rail_products', function (Blueprint $table) {
            $table->dropColumn(['line', 'code', 'description', 'mountings']);
        });
    }
};
