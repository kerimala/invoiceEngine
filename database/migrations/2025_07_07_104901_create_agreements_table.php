<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('version');
            $table->string('strategy');
            $table->decimal('multiplier', 8, 4);
            $table->decimal('vat_rate', 5, 4);
            $table->string('currency', 3);
            $table->string('locale', 2);
            $table->json('rules');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
