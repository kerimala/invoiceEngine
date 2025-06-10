<?php

namespace Packages\PricingEngine\Services;

use Packages\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

class PricingEngineService
{
    public function priceLine(array $parsedLine, array $agreement): array
    {
        $this->validateInputs($parsedLine, $agreement);

        $multiplier = $agreement['multiplier'] ?? 100;

        if ($multiplier < 0) {
            throw new InvalidArgumentException('Agreement multiplier must be non-negative');
        }

        // Handle multiplier as 1.2 for 120%
        if ($multiplier > 0 && $multiplier < 5) { // Heuristic for float multiplier
             $multiplier = $multiplier * 100;
        }


        $quantity = $parsedLine['quantity'];
        $unitPrice = $parsedLine['unit_price'];

        $lineTotal = (int) round(($quantity * $unitPrice * $multiplier) / 100);

        return array_merge($parsedLine, [
            'line_total' => $lineTotal,
            'agreement_version' => $agreement['version'],
            'currency' => $agreement['currency'] ?? null,
            'language' => $agreement['language'] ?? null,
        ]);
    }

    public function priceLines(array $lines, array $agreement): array
    {
        return array_map(fn($line) => $this->priceLine($line, $agreement), $lines);
    }

    public function priceLineAndEmit(array $parsedLine, array $agreement)
    {
        $pricedLine = $this->priceLine($parsedLine, $agreement);
        $event = new PricedInvoiceLine($pricedLine);
        Event::dispatch($event);
        return $event;
    }

    private function validateInputs(array $parsedLine, array $agreement): void
    {
        if (empty($agreement['version'])) {
            throw new InvalidArgumentException('Agreement version is required');
        }

        if (!isset($agreement['multiplier'])) {
            // This is now handled by the default value in priceLine
            // throw new InvalidArgumentException('Agreement multiplier is required');
        }


        if (empty($parsedLine['quantity'])) {
            throw new InvalidArgumentException('quantity is required');
        }

        if (empty($parsedLine['unit_price'])) {
            throw new InvalidArgumentException('unit_price is required');
        }
    }

    public function calculatePrice(array $invoiceLine): float
    {
        // Pricing logic will go here
        return 0.0;
    }
} 