<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Textil pro rolety: látky dodavatele s cenovou skupinou A–D. Cenová tabulka
 * rolety (šířka × výška) je teď zvlášť pro každou cenovou skupinu látky.
 * Stávající ceny (zadané ještě bez skupiny) připadnou skupině A.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shade_fabrics', function (Blueprint $table) {
            $table->id();
            $table->string('supplier', 30)->nullable();
            $table->string('code')->unique();               // objednací číslo, např. „Col 100“
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('collection', 20);               // ShadeFabricCollection: standard, screen, magico, roma
            $table->char('price_category', 1);              // ShadePriceCategory: A–D
            $table->unsignedSmallInteger('roll_width')->nullable(); // šíře látky [cm] = největší šířka rolety
            $table->json('specs')->nullable();              // [{label, value}] – vlastnosti od dodavatele
            $table->string('image_path')->nullable();
            $table->string('source_image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['collection', 'price_category']);
        });

        Schema::table('shade_variants', function (Blueprint $table) {
            $table->json('fabric_collections')->nullable()->after('step'); // z jakých kolekcí látek se provedení šije
        });

        Schema::table('shade_prices', function (Blueprint $table) {
            $table->dropUnique(['shade_variant_id', 'width', 'height']);
        });

        Schema::table('shade_prices', function (Blueprint $table) {
            $table->char('price_category', 1)->default('A')->after('shade_variant_id');
            $table->unique(['shade_variant_id', 'price_category', 'width', 'height']);
        });
    }

    public function down(): void
    {
        Schema::table('shade_prices', function (Blueprint $table) {
            $table->dropUnique(['shade_variant_id', 'price_category', 'width', 'height']);
        });

        Schema::table('shade_prices', function (Blueprint $table) {
            $table->dropColumn('price_category');
            $table->unique(['shade_variant_id', 'width', 'height']);
        });

        Schema::table('shade_variants', function (Blueprint $table) {
            $table->dropColumn('fabric_collections');
        });

        Schema::dropIfExists('shade_fabrics');
    }
};
