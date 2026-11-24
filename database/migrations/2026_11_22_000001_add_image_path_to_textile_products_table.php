<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fotka produktu z ceníku textilu, nahraná v administraci.
 * Cesta na výchozím disku (FILESYSTEM_DISK), web ji servíruje přes route web.textile-image.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('colors');
        });
    }

    public function down(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
