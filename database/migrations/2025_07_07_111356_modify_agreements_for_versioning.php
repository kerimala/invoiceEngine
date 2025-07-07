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
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
            $table->timestamp('valid_from')->after('rules')->nullable();
            $table->unique(['customer_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->unique(['customer_id']);
            $table->dropColumn('valid_from');
            $table->dropUnique(['customer_id', 'version']);
        });
    }
};
