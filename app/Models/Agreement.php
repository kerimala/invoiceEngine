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
        'language',
        'rules',
        'valid_from',
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
