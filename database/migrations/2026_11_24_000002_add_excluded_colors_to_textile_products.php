<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barvy od dodavatele, které dílna v administraci smazala. Import Laguny je
 * podle tohoto seznamu znovu nezaloží.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->json('excluded_colors')->nullable()->after('specs');
        });
    }

    public function down(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->dropColumn('excluded_colors');
        });
    }
};