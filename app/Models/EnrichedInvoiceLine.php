<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrichedInvoiceLine extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $casts = [
        'raw_line' => 'array',
        'processing_metadata' => 'array',
    ];

    protected $fillable = [
        'raw_line',
        'nett_total',
        'vat_amount',
        'line_total',
        'currency',
        'agreement_version',
        'agreement_type',
        'pricing_strategy',
        'processing_metadata',
    ];
}
