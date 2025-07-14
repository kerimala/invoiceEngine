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
            // Company information fields
            $table->string('invoicing_company_name')->nullable()->after('locale');
            $table->text('invoicing_company_address')->nullable()->after('invoicing_company_name');
            $table->string('invoicing_company_phone')->nullable()->after('invoicing_company_address');
            $table->string('invoicing_company_email')->nullable()->after('invoicing_company_phone');
            $table->string('invoicing_company_website')->nullable()->after('invoicing_company_email');
            $table->string('invoicing_company_vat_number')->nullable()->after('invoicing_company_website');
            
            // Logo path field
            $table->string('logo_path')->nullable()->after('invoicing_company_vat_number');
            
            // Invoice metadata
            $table->string('invoice_number_prefix')->nullable()->after('logo_path');
            $table->text('invoice_footer_text')->nullable()->after('invoice_number_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn([
                'invoicing_company_name',
                'invoicing_company_address', 
                'invoicing_company_phone',
                'invoicing_company_email',
                'invoicing_company_website',
                'invoicing_company_vat_number',
                'logo_path',
                'invoice_number_prefix',
                'invoice_footer_text'
            ]);
        });
    }
};