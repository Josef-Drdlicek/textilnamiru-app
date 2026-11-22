<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Barvy produktu jako samostatné řádky s vlastní fotkou (zákazník vybere barvu,
 * změní se fotka) a údaje o dodavateli pro import katalogu.
 * Cena za metr může chybět — importované látky ji dostanou až od dílny.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textile_product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textile_product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                         // např. „Bílá“ nebo kód dodavatele „03“
            $table->string('image_path')->nullable();       // fotka na výchozím disku
            $table->string('source_image_url')->nullable(); // odkud se fotka stáhla (import)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['textile_product_id', 'name']);
        });

        Schema::table('textile_products', function (Blueprint $table) {
            $table->string('supplier', 30)->nullable()->after('slug');
            $table->string('supplier_code', 60)->nullable()->unique()->after('supplier'); // např. 11101/180
            $table->json('specs')->nullable()->after('description');                     // další parametry [[popis, hodnota], …]
            $table->decimal('price_per_meter', 10, 2)->nullable()->change();
        });

        // Dosavadní barvy (seznam názvů) převést na řádky.
        foreach (DB::table('textile_products')->get(['id', 'colors']) as $product) {
            foreach (array_values(json_decode($product->colors ?? '[]', true) ?: []) as $index => $name) {
                DB::table('textile_product_colors')->insert([
                    'textile_product_id' => $product->id,
                    'name' => $name,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('textile_products', function (Blueprint $table) {
            $table->dropColumn('colors');
        });
    }

    public function down(): void
    {
        Schema::table('textile_products', function (Blueprint $table) {
            $table->json('colors')->nullable();
            $table->dropUnique(['supplier_code']);
            $table->dropColumn(['supplier', 'supplier_code', 'specs']);
        });

        Schema::dropIfExists('textile_product_colors');
    }
};
