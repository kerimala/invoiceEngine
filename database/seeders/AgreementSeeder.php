<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgreementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('agreements')->insert([
            'customer_id' => 'customer-123',
            'version' => 'v1.2',
            'strategy' => 'standard',
            'multiplier' => 1.15,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => json_encode([
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => 'Charge',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
