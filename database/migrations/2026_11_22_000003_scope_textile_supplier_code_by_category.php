<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stejné označení dodavatele může mít záclona i dekorační látka (závěs),
 * kód je proto jedinečný jen v rámci kategorie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->dropUnique(['supplier_code']);
            $table->unique(['category', 'supplier_code']);
        });
    }

    public function down(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->dropUnique(['category', 'supplier_code']);
            $table->unique(['supplier_code']);
        });
    }
};
