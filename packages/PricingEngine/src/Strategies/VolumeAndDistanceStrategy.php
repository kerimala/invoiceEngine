<?php

namespace InvoicingEngine\PricingEngine\Strategies;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

/**
 * Volume and distance pricing strategy with flexible column mapping support.
 * 
 * Supports configurable column names through agreement rules:
 * - volume_column: Column name for volume calculation (default: 'Volume')
 * - distance_column: Column name for distance calculation (default: 'Distance')
 */
class VolumeAndDistanceStrategy implements PricingStrategyInterface
{
    public function calculate(array $invoiceLine, array $agreement): array
    {
        $rules = $agreement['rules'];

        // Use flexible column mapping for volume and distance
        $volumeColumn = $rules['volume_column'] ?? 'Volume';
        $distanceColumn = $rules['distance_column'] ?? 'Distance';
        
        $volume = $invoiceLine[$volumeColumn] ?? 0;
        $distance = $invoiceLine[$distanceColumn] ?? 0;

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