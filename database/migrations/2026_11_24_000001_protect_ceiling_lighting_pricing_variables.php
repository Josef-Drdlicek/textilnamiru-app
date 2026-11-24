<?php

use Database\Seeders\CeilingPricingVariableSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Položky ceníku stropů, které čte vzorec světelné lišty, nesmí jít smazat:
 * bez nich kalkulačka stropu i uložení zakázky skončí chybou 500.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('pricing_variables')
            ->whereIn('code', CeilingPricingVariableSeeder::SYSTEM_CODES)
            ->update(['is_system' => true]);
    }

    public function down(): void
    {
        DB::table('pricing_variables')
            ->whereIn('code', CeilingPricingVariableSeeder::SYSTEM_CODES)
            ->update(['is_system' => false]);
    }
};