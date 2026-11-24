<?php

use Database\Seeders\CeilingPricingVariableSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ceník se dělí na kategorie (šití, stropy). Stávající řádky zůstávají
 * v ceníku šití, nový ceník stropů dostane položky zadané majitelem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_variables', function (Blueprint $table) {
            $table->string('category', 20)->default('sewing')->after('code')->index();
        });

        (new CeilingPricingVariableSeeder)->run();
    }

    public function down(): void
    {
        DB::table('pricing_variables')->where('category', 'ceiling')->delete();

        Schema::table('pricing_variables', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
