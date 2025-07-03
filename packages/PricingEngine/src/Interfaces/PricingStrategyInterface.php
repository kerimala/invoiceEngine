<?php

namespace InvoicingEngine\PricingEngine\Interfaces;

/**
 * Interface for pricing strategies.
 * All pricing strategies must implement this interface.
 */
interface PricingStrategyInterface
{
    /**
     * Calculates the price for a single invoice line based on a set of rules.
     *
     * @param array $invoiceLine The invoice line data.
     * @param array $agreement The customer agreement containing pricing rules.
     * @return array The priced invoice line.
     */
    public function calculate(array $invoiceLine, array $agreement): array;
}