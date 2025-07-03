<?php

namespace InvoicingEngine\PricingEngine\Services;

use InvoicingEngine\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class PricingEngineService
{
    public function priceLine(array $parsedLine, array $agreement): array
    {
        Log::info('Pricing a single line.', ['agreement_version' => $agreement['version']]);
        $this->validateAgreement($agreement);

        $strategy = PricingStrategyFactory::create($agreement);
        $pricedLine = $strategy->calculate($parsedLine, $agreement);

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

        // More specific validation should be handled by the strategy itself.
        if (empty($agreement['rules'])) {
            Log::error('Agreement validation failed: rules are missing.');
            throw new InvalidArgumentException('Agreement must contain a ruleset.');
        }
        Log::debug('Agreement validation passed.');
    }

    public function calculatePrice(array $invoiceLine): float
    {
        // Pricing logic will go here
        return 0.0;
    }
}