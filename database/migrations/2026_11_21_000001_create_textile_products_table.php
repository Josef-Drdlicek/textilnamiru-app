<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ceník textilu: látky (záclony, závěsy) s cenou za běžný metr.
 * Z nich si zákazník na produktové stránce nechá ušít výrobek na míru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textile_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 20)->index();
            $table->text('description')->nullable();
            $table->string('material')->nullable();          // např. 100% polyester
            $table->unsignedSmallInteger('roll_height');      // výška (šíře) role [cm] = max. výška výrobku
            $table->string('shrinkage', 20)->nullable();      // srážení, např. ±1 %
            $table->unsignedSmallInteger('delivery_days')->nullable();
            $table->decimal('price_per_meter', 10, 2);        // Kč za běžný metr bez DPH
            $table->json('colors');                           // nabízené barvy
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        // Zkušební produkt zakládá TextileProductSeeder (DatabaseSeeder) – až po všech migracích textilu.
    }

    public function down(): void
    {
        Schema::dropIfExists('textile_products');
    }
};
