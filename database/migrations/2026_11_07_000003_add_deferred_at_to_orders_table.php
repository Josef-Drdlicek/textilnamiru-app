<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cenová nabídka = rozpracovaná zakázka, kterou studio odložilo
 * (typicky čeká na souhlas svého klienta) a vrátí se k ní později.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('deferred_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('deferred_at');
        });
    }
};
