<?php

namespace Database\Seeders;

use App\Enums\TextileCategory;
use App\Models\TextileProduct;
use Illuminate\Database\Seeder;

/**
 * Zkušební produkt pro odladění produktové stránky a kalkulace.
 * Idempotentní: existující produkt (a ceny upravené dílnou) nechá být.
 */
class TextileProductSeeder extends Seeder
{
    public function run(): void
    {
        $product = TextileProduct::firstOrCreate(['slug' => 'zaclona-test-001'], [
            'name' => 'Záclona test 001',
            'category' => TextileCategory::Curtain,
            'description' => 'Zkušební záclona pro odladění produktové stránky. Lehká záclona s jemnou vazbou, dobře propouští světlo a hodí se do moderních interiérů.',
            'material' => '100% polyester',
            'roll_height' => 300,
            'shrinkage' => '±1 %',
            'delivery_days' => 20,
            'price_per_meter' => 300,
            'is_active' => true,
        ]);

        if ($product->wasRecentlyCreated) {
            foreach (['Bílá', 'Přírodní', 'Stříbrná', 'Slonová kost'] as $index => $name) {
                $product->colors()->create(['name' => $name, 'sort_order' => $index]);
            }
        }
    }
}
