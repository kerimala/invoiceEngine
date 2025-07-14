# Calculator Service Implementation

This document outlines how the requirements for the Calculator Service are implemented in this project.

## Acceptance Criteria Implementation

### Microservice for calculation of pricing

Pricing calculation is handled by the `PricingEngine` package located in `packages/PricingEngine`.

The main service is `InvoicingEngine\PricingEngine\Services\PricingEngineService`. It uses a factory pattern to select a pricing strategy based on the customer agreement.

The relevant files are:
- `packages/PricingEngine/src/Services/PricingEngineService.php`
- `packages/PricingEngine/src/Services/PricingStrategyFactory.php`

### Microservice for calculating distance

Distance calculation is part of the `VolumeAndDistanceStrategy` pricing strategy. It's not a separate microservice but is integrated into the pricing calculation when this strategy is used.

The calculation is performed in:
- `packages/PricingEngine/src/Strategies/VolumeAndDistanceStrategy.php`

The `calculate` method in this class uses the `distance_column` from the invoice line and the `distance_rate` from the agreement rules.

### Microservice for calculating weight

Weight calculation is part of the `StandardPricingStrategy`. Similar to distance, it's integrated into the pricing logic.

The calculation is performed in:
- `packages/PricingEngine/src/Strategies/StandardPricingStrategy.php`

The `calculate` method uses the `Weight Charge` from the invoice line.

### Microservice converting float to INT

The `UnitConverter` microservice has been fully implemented to handle float-to-integer conversions for various units. This service is located in the `packages/UnitConverter` directory and provides the following methods:

- toCents(float $amount): int - Converts currency amounts to cents
- toNanograms(float $weight): int - Converts weight in grams to nanograms
- toMillimeters(float $distance): int - Converts distance in meters to millimeters

The service has been thoroughly tested with unit tests located in `packages/UnitConverter/tests/UnitConverterServiceTest.php`. The tests verify:
- Correct conversion of currency values to cents
- Accurate conversion of weights to nanograms
- Proper distance conversion to millimeters

The microservice is now fully integrated into the project's autoloader and can be used throughout the application.