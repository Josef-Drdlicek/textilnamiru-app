<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rolety (stínicí technika): produkt (např. Roleta Leontina) má varianty
 * (Leontina, Leontina Box), každá s texty, fotkami a cenovou tabulkou
 * podle šířky × výšky (krok zpravidla 10 cm). Ceny doplňuje dílna.
 *
 * Názvy „shade_*“, aby se nepletly s římskou roletou (ItemType::Blind), kterou šijeme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shade_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('shade_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shade_product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('supplier', 30)->nullable();
            $table->string('supplier_url')->nullable()->unique();
            $table->string('subtitle')->nullable();       // např. „roleta s řetízkovým mechanismem“
            $table->string('slogan')->nullable();
            $table->text('description')->nullable();      // odstavce oddělené prázdným řádkem
            $table->json('technical')->nullable();        // [{type: heading|list|text, …}]
            $table->unsignedSmallInteger('min_width')->default(40);
            $table->unsignedSmallInteger('max_width')->default(250);
            $table->unsignedSmallInteger('min_height')->default(40);
            $table->unsignedSmallInteger('max_height')->default(250);
            $table->unsignedSmallInteger('step')->default(10);   // krok cenové tabulky [cm]
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shade_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shade_variant_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('source_url')->nullable();
            $table->string('alt')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['shade_variant_id', 'source_url']);
        });

        Schema::create('shade_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shade_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('width');   // [cm] – cena platí do této šířky
            $table->unsignedSmallInteger('height');  // [cm] – a do této výšky
            $table->decimal('price', 10, 2);         // Kč bez DPH za kus
            $table->timestamps();

            $table->unique(['shade_variant_id', 'width', 'height']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shade_prices');
        Schema::dropIfExists('shade_variant_images');
        Schema::dropIfExists('shade_variants');
        Schema::dropIfExists('shade_products');
    }
};
