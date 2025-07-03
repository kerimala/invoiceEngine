<?php

namespace InvoicingEngine\PricingEngine\Strategies;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

class VolumeAndDistanceStrategy implements PricingStrategyInterface
{
    public function calculate(array $invoiceLine, array $agreement): array
    {
        $rules = $agreement['rules'];

        $volume = $invoiceLine[$rules['volume_column']];
        $distance = $invoiceLine[$rules['distance_column']];

        $baseRate = $rules['base_rate'];
        $volumeRate = $rules['volume_rate'];
        $distanceRate = $rules['distance_rate'];

        $nettTotal = $baseRate + ($volume * $volumeRate) + ($distance * $distanceRate);

        $vatAmount = $nettTotal * $agreement['vat_rate'];
        $lineTotal = $nettTotal + $vatAmount;

        return array_merge($invoiceLine, [
            'nett_total' => $nettTotal,
            'vat_amount' => $vatAmount,
            'line_total' => $lineTotal,
        ]);
    }
}