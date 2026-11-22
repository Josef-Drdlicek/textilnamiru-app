<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zavěšení textilu: kolejnice a dekorační garnýže. Produkt (např. N-Line, Quadro 35)
 * má varianty (N1 Napoli…, u garnýží barvy). Cena varianty se řídí délkou
 * v pásmech po 50 cm do 6 m (1–50, 51–100 … 551–600 cm). Ceny doplňuje dílna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rail_products', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);                 // RailKind: rail, decorative
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->json('features')->nullable();       // [text] – společné pro všechny varianty
            $table->json('specs')->nullable();          // [{label, value}]
            $table->json('components')->nullable();     // [{code, name, group, image}] – jezdci, držáky, montáž
            $table->string('supplier', 30)->nullable();
            $table->string('supplier_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('rail_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rail_product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->text('description')->nullable();    // odstavce oddělené prázdným řádkem
            $table->json('features')->nullable();
            $table->json('specs')->nullable();
            $table->json('components')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['rail_product_id', 'name']);
        });

        Schema::create('rail_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rail_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rail_variant_id')->nullable()->constrained()->cascadeOnDelete(); // null = fotka celého produktu
            $table->string('role', 20)->default('photo'); // photo | component
            $table->string('image_path')->nullable();
            $table->string('source_url')->nullable();
            $table->string('alt')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rail_product_id', 'source_url']);
        });

        Schema::create('rail_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rail_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('length_to');  // [cm] – horní mez pásma (50, 100 … 600)
            $table->decimal('price', 10, 2);            // Kč bez DPH za kus dané délky
            $table->timestamps();

            $table->unique(['rail_variant_id', 'length_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rail_prices');
        Schema::dropIfExists('rail_images');
        Schema::dropIfExists('rail_variants');
        Schema::dropIfExists('rail_products');
    }
};
