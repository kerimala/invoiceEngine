<?php

namespace Packages\PricingEngine\Services;

use Packages\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

class PricingEngineService
{
    public function priceLine(array $parsedLine, array $agreement): array
    {
        $this->validateAgreement($agreement);

        $rules = $agreement['rules'];
        $multiplier = $agreement['multiplier'] ?? 1;

        $baseCharge = (float) ($parsedLine[$rules['base_charge_column']] ?? 0);

        $surchargeTotal = 0;
        foreach ($parsedLine as $key => $value) {
            if (str_starts_with($key, $rules['surcharge_prefix']) && str_ends_with($key, $rules['surcharge_suffix'])) {
                $surchargeTotal += (float) $value;
            }
        }
        
        $lineTotal = ($baseCharge + $surchargeTotal) * $multiplier;

        return array_merge($parsedLine, [
            'line_total' => round($lineTotal, 2),
            'agreement_version' => $agreement['version'],
            'currency' => $agreement['currency'] ?? 'EUR',
        ]);
    }

    public function priceLines(array $lines, array $agreement): array
    {
        return array_map(fn($line) => $this->priceLine($line, $agreement), $lines);
    }

    public function priceLineAndEmit(array $parsedLine, array $agreement, string $filePath)
    {
        $pricedLine = $this->priceLine($parsedLine, $agreement);
        $event = new PricedInvoiceLine($pricedLine, $filePath);
        Event::dispatch($event);
        return $event;
    }

    private function validateAgreement(array $agreement): void
    {
        if (empty($agreement['version'])) {
            throw new InvalidArgumentException('Agreement version is required');
        }

        if (empty($agreement['rules']['base_charge_column'])) {
            throw new InvalidArgumentException('Base charge column rule is required in agreement');
        }

        if (empty($agreement['rules']['surcharge_prefix'])) {
            throw new InvalidArgumentException('Surcharge prefix rule is required in agreement');
        }
        
        if (empty($agreement['rules']['surcharge_suffix'])) {
            throw new InvalidArgumentException('Surcharge suffix rule is required in agreement');
        }
    }

    public function calculatePrice(array $invoiceLine): float
    {
        // Pricing logic will go here
        return 0.0;
    }
} 