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
        Schema::create('enriched_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->json('raw_line');
            $table->decimal('nett_total', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->string('currency');
            $table->string('agreement_version');
            $table->string('agreement_type');
            $table->string('pricing_strategy');
            $table->json('processing_metadata');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enriched_invoice_lines');
    }
};
