<?php

namespace InvoicingEngine\PricingEngine\Strategies;

use InvalidArgumentException;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

/**
 * Tiered pricing strategy with flexible column mapping support.
 * 
 * Supports configurable column names through agreement rules:
 * - quantity_column: Column name for quantity calculation (default: 'Quantity')
 */
class TieredPricingStrategy implements PricingStrategyInterface
{
    public function calculate(array $invoiceLine, array $agreement): array
    {
        $rules = $agreement['rules'];
        // Use flexible column mapping for quantity
        $quantityColumn = $rules['quantity_column'] ?? 'Quantity';
        $quantity = $invoiceLine[$quantityColumn];
        $tiers = $rules['tiers'];

        if (empty($tiers)) {
            throw new InvalidArgumentException('Tiers not defined for tiered pricing strategy.');
        }

        $nettTotal = 0;
        $remainingQuantity = $quantity;

        foreach ($tiers as $tier) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $tierQuantity = $tier['up_to'];
            $tierRate = $tier['rate'];

            $quantityInTier = min($remainingQuantity, $tierQuantity);
            $nettTotal += $quantityInTier * $tierRate;
            $remainingQuantity -= $quantityInTier;
        }

        // If there's any remaining quantity, it means it's beyond the defined tiers.
        // Depending on business logic, you might want to handle this differently.
        // For now, we assume the tiers cover all possible quantities.

        $vatAmount = $nettTotal * $agreement['vat_rate'];
        $lineTotal = $nettTotal + $vatAmount;

        return array_merge($invoiceLine, [
            'nett_total' => $nettTotal,
            'vat_amount' => $vatAmount,
            'line_total' => $lineTotal,
        ]);
    }
}