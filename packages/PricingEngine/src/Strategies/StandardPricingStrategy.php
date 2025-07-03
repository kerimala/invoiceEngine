<?php

namespace InvoicingEngine\PricingEngine\Strategies;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

use Illuminate\Support\Facades\Log;

class StandardPricingStrategy implements PricingStrategyInterface
{
    /**
     * Calculates the price for a single invoice line using the standard pricing model.
     * This model calculates the price based on a base charge and a sum of surcharges, multiplied by a factor.
     *
     * @param array $invoiceLine The invoice line data.
     * @param array $agreement The customer agreement containing pricing rules.
     * @return array The priced invoice line.
     */
    public function calculate(array $invoiceLine, array $agreement): array
    {
        $rules = $agreement['rules'];
        $multiplier = $agreement['multiplier'] ?? 1;

        $baseChargeColumn = $rules['base_charge_column'];
        $baseCharge = (float) ($invoiceLine[$baseChargeColumn] ?? 0);

        $surchargeTotal = 0;
        foreach ($invoiceLine as $key => $value) {
            if (str_starts_with($key, $rules['surcharge_prefix']) && str_ends_with($key, $rules['surcharge_suffix'])) {
                $surchargeTotal += (float) $value;
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