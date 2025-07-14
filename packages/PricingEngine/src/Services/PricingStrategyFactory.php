<?php

namespace InvoicingEngine\PricingEngine\Services;

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;
use InvoicingEngine\PricingEngine\Strategies\StandardPricingStrategy;
use InvoicingEngine\PricingEngine\Strategies\TieredPricingStrategy;
use InvoicingEngine\PricingEngine\Strategies\VolumeAndDistanceStrategy;
use InvalidArgumentException;

class PricingStrategyFactory
{
    /**
     * Creates a pricing strategy instance based on the agreement.
     *
     * @param array $agreement The customer agreement.
     * @return PricingStrategyInterface The pricing strategy instance.
     * @throws InvalidArgumentException If the strategy is not supported.
     */
    public static function create(array $agreement): PricingStrategyInterface
    {
        $strategyName = $agreement['strategy'] ?? 'standard';

        switch ($strategyName) {
            case 'standard':
                return new StandardPricingStrategy();
            case 'tiered':
                return new TieredPricingStrategy();
            case 'volume_and_distance':
                return new VolumeAndDistanceStrategy();
            default:
                throw new InvalidArgumentException("Unsupported pricing strategy: {$strategyName}");
        }
    }
}