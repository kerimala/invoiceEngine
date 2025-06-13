<?php

namespace Packages\PricingEngine\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\AgreementService\Services\AgreementService;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Packages\PricingEngine\Services\PricingEngineService;
use Illuminate\Support\Facades\Event;

class ApplyPricing implements ShouldQueue
{
    public function __construct(
        private readonly PricingEngineService $pricingEngineService,
        private readonly AgreementService $agreementService
    ) {
    }

    public function handle(CarrierInvoiceLineExtracted $event): void
    {
        // In a real scenario, you'd extract a customer ID from the file path or metadata
        $customerId = 'some_customer_id'; 
        $agreement = $this->agreementService->getAgreementForCustomer($customerId);

        $lineCount = count($event->parsedLines);
        foreach ($event->parsedLines as $index => $line) {
            $pricedLine = $this->pricingEngineService->priceLine($line, $agreement);

            if ($index === $lineCount - 1) {
                $pricedLine['last_line'] = true;
            }

            Event::dispatch(new PricedInvoiceLine($pricedLine, $event->filePath));
        }
    }
} 