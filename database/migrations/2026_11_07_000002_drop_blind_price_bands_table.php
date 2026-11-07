<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Římská roleta se nově počítá vzorcem z ceníkových položek
 * (KOMPLET_ROLETA, TUNYLKY_ROLETA, PRIPRAVA, USITI_ZALOZKA),
 * tabulka pásem šířka × výška už nemá využití.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('blind_price_bands');
    }

    public function down(): void
    {
        Schema::create('blind_price_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('max_width');
            $table->unsignedInteger('max_height');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['max_width', 'max_height']);
        });
    }
};
