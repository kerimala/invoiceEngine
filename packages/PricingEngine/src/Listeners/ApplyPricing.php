<?php

namespace Packages\PricingEngine\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Packages\PricingEngine\Services\PricingEngineService;
use Illuminate\Support\Facades\Event;

class ApplyPricing implements ShouldQueue
{
    public function __construct(private readonly PricingEngineService $pricingEngineService)
    {
    }

    public function handle(CarrierInvoiceLineExtracted $event): void
    {
        // This is a simplification. In a real scenario, you'd fetch the
        // correct agreement for the customer associated with the invoice.
        $agreement = ['version' => 'v1', 'multiplier' => 100]; // Default agreement

        foreach ($event->parsedLines as $line) {
            $pricedLine = $this->pricingEngineService->priceLine($line, $agreement);
            Event::dispatch(new PricedInvoiceLine($pricedLine, $event->filePath));
        }
    }
} 