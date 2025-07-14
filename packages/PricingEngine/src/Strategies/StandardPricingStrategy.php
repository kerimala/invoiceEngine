<?php

namespace InvoicingEngine\PricingEngine\Strategies;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

use Illuminate\Support\Facades\Log;

/**
 * Standard pricing strategy with flexible column mapping support.
 * 
 * Supports configurable column names through agreement rules:
 * - base_charge_column: Column name for base charge (default: 'Weight Charge')
 * - surcharge_columns: Array of column names for surcharges (falls back to auto-detection if not specified)
 */
class StandardPricingStrategy implements PricingStrategyInterface
{
    /**
     * Calculates the price for a single invoice line using the standard pricing model.
     * This model calculates the price based on a base charge and a sum of surcharges, multiplied by a factor.
     * Supports configurable column names through agreement rules.
     *
     * @param array $invoiceLine The invoice line data.
     * @param array $agreement The customer agreement containing pricing rules.
     * @return array The priced invoice line.
     */
    public function calculate(array $invoiceLine, array $agreement): array
    {

        $multiplier = $agreement['multiplier'] ?? 1;

        // Use flexible column mapping for base charge
        $baseChargeColumn = $agreement['base_charge_column'] ?? 'Weight Charge';
        $baseCharge = (float) ($invoiceLine[$baseChargeColumn] ?? 0);

        // Use flexible column mapping for surcharges
        $surchargeColumns = $agreement['surcharge_columns'] ?? [];
        $surchargeTotal = 0;
        
        if (!empty($surchargeColumns)) {
            // Use configured surcharge columns
            foreach ($surchargeColumns as $column) {
                if (isset($invoiceLine[$column]) && is_numeric($invoiceLine[$column])) {
                    $surchargeTotal += (float) $invoiceLine[$column];
                }
            }
        } else {
            // Fallback to original logic if no surcharge columns configured
            foreach ($invoiceLine as $key => $value) {
                if (str_ends_with($key, 'Charge') && $key !== $baseChargeColumn && is_numeric($value)) {
                    $surchargeTotal += (float) $value;
                }
            }
        }

        // The nett total is the sum of the base charge and all surcharges, multiplied by a customer-specific multiplier.
        $nettTotal = ($baseCharge + $surchargeTotal) * $multiplier;

        $vatRate = $agreement['vat_rate'] ?? 0;
        $vatAmount = $nettTotal * $vatRate;

        $lineTotal = $nettTotal + $vatAmount;

        $pricedLine = array_merge($invoiceLine, [
            'nett_total' => round($nettTotal, 2),
            'vat_amount' => round($vatAmount, 2),
            'line_total' => round($lineTotal, 2),
            'agreement_version' => $agreement['version'],
            'currency' => $agreement['currency'] ?? 'EUR',
        ]);

        Log::info('Line priced successfully using StandardPricingStrategy.', ['priced_line' => $pricedLine]);

        return $pricedLine;
    }
}