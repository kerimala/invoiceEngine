<?php

namespace Packages\PricingEngine\Services;

use Packages\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class PricingEngineService
{
    public function priceLine(array $parsedLine, array $agreement): array
    {
        Log::info('Pricing a single line.', ['agreement_version' => $agreement['version']]);
        $this->validateAgreement($agreement);

        $rules = $agreement['rules'];
        $multiplier = $agreement['multiplier'] ?? 1;
        Log::debug('Applying pricing rules.', ['rules' => $rules, 'multiplier' => $multiplier]);

        $baseChargeColumn = $rules['base_charge_column'];
        if (!isset($parsedLine[$baseChargeColumn])) {
            Log::warning('Base charge column not found in parsed line.', ['column' => $baseChargeColumn, 'line' => $parsedLine]);
        }
        $baseCharge = (float) ($parsedLine[$baseChargeColumn] ?? 0);

        $surchargeTotal = 0;
        foreach ($parsedLine as $key => $value) {
            if (str_starts_with($key, $rules['surcharge_prefix']) && str_ends_with($key, $rules['surcharge_suffix'])) {
                $surchargeTotal += (float) $value;
                Log::debug('Surcharge added.', ['key' => $key, 'value' => $value, 'current_total' => $surchargeTotal]);
            }
        }
        
        $nettTotal = ($baseCharge + $surchargeTotal) * $multiplier;
        Log::info('Nett total calculated.', ['base_charge' => $baseCharge, 'surcharge_total' => $surchargeTotal, 'multiplier' => $multiplier, 'nett_total' => $nettTotal]);

        $vatRate = $agreement['vat_rate'] ?? 0;
        $vatAmount = $nettTotal * $vatRate;
        Log::info('VAT calculated.', ['nett_total' => $nettTotal, 'vat_rate' => $vatRate, 'vat_amount' => $vatAmount]);

        $lineTotal = $nettTotal + $vatAmount;
        Log::info('Line total calculated.', ['nett_total' => $nettTotal, 'vat_amount' => $vatAmount, 'total' => $lineTotal]);

        $pricedLine = array_merge($parsedLine, [
            'nett_total' => round($nettTotal, 2),
            'vat_amount' => round($vatAmount, 2),
            'line_total' => round($lineTotal, 2),
            'agreement_version' => $agreement['version'],
            'currency' => $agreement['currency'] ?? 'EUR',
        ]);

        Log::info('Line priced successfully.', ['priced_line' => $pricedLine]);
        return $pricedLine;
    }

    public function priceLines(array $lines, array $agreement): array
    {
        Log::info('Pricing multiple lines.', ['line_count' => count($lines), 'agreement_version' => $agreement['version']]);
        $pricedLines = array_map(fn($line) => $this->priceLine($line, $agreement), $lines);
        Log::info('Finished pricing multiple lines.', ['processed_count' => count($pricedLines)]);
        return $pricedLines;
    }

    public function priceLineAndEmit(array $parsedLine, array $agreement, string $filePath)
    {
        Log::info('Pricing line and emitting event.', ['filePath' => $filePath]);
        $pricedLine = $this->priceLine($parsedLine, $agreement);
        $event = new PricedInvoiceLine($pricedLine, $filePath);
        Log::info('Dispatching PricedInvoiceLine event.', ['filePath' => $filePath, 'priced_line' => $pricedLine]);
        Event::dispatch($event);
        return $event;
    }

    private function validateAgreement(array $agreement): void
    {
        Log::debug('Validating agreement.', ['agreement_version' => $agreement['version'] ?? 'N/A']);
        if (empty($agreement['version'])) {
            Log::error('Agreement validation failed: version is missing.');
            throw new InvalidArgumentException('Agreement version is required');
        }

        if (empty($agreement['rules']['base_charge_column'])) {
            Log::error('Agreement validation failed: base_charge_column is missing.');
            throw new InvalidArgumentException('Base charge column rule is required in agreement');
        }

        if (empty($agreement['rules']['surcharge_prefix'])) {
            Log::error('Agreement validation failed: surcharge_prefix is missing.');
            throw new InvalidArgumentException('Surcharge prefix rule is required in agreement');
        }
        
        if (empty($agreement['rules']['surcharge_suffix'])) {
            Log::error('Agreement validation failed: surcharge_suffix is missing.');
            throw new InvalidArgumentException('Surcharge suffix rule is required in agreement');
        }
        Log::debug('Agreement validation passed.');
    }

    public function calculatePrice(array $invoiceLine): float
    {
        // Pricing logic will go here
        return 0.0;
    }
}