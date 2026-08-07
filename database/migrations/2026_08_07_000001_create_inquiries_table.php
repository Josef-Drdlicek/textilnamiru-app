<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('new');
            $table->string('name', 100);
            $table->string('email');
            $table->string('phone', 30);
            $table->string('city', 100)->nullable();
            $table->string('space_type')->nullable();
            $table->json('products')->nullable();
            $table->string('measurement')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('gdpr_consented_at');
            // Snapshot konfigurace z veřejné kalkulačky (typ, parametry,
            // orientační cena) — staví se výhradně na serveru.
            $table->json('configuration')->nullable();
            $table->string('ip', 45)->nullable();
            // Vyplněno při převzetí poptávky do zakázky.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
