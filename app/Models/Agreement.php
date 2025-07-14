<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected static function booted(): void
    {
        static::updating(function (self $agreement) {
            // Prevent modification of existing agreements.
            return false;
        });
    }

    use HasFactory;

    protected $table = 'agreements';

    protected $fillable = [
        'customer_id',
        'version',
        'strategy',
        'multiplier',
        'vat_rate',
        'currency',
        'locale',
        'invoice_language',
        'fallback_language',
        'invoicing_company_name',
        'invoicing_company_address',
        'invoicing_company_phone',
        'invoicing_company_email',
        'invoicing_company_website',
        'invoicing_company_vat_number',
        'logo_path',
        'invoice_number_prefix',
        'invoice_footer_text',
        'rules',
        'valid_from',
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
