<?php

namespace InvoicingEngine\PricingEngine\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\AgreementService\Services\AgreementService;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Events\PricedInvoiceLine;
use InvoicingEngine\PricingEngine\Services\PricingEngineService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ApplyPricing implements ShouldQueue
{
    public function __construct(
        private readonly PricingEngineService $pricingEngineService,
        private readonly AgreementService $agreementService
    ) {
    }

    public function handle(CarrierInvoiceLineExtracted $event): void
    {
        Log::info('ApplyPricing listener started.', ['filePath' => $event->filePath, 'parsedLines' => $event->parsedLines]);

        // In a real scenario, you'd extract a customer ID from the file path or metadata
        $customerId = $event->parsedLines[0]['Billing Account'] ?? 'some_customer_id'; 
        Log::info('Extracted customer ID for agreement.', ['customerId' => $customerId, 'filePath' => $event->filePath]);

        $agreement = $this->agreementService->getAgreementForCustomer($customerId);

        if (!$agreement) {
            Log::error('No agreement found for customer.', ['customerId' => $customerId, 'filePath' => $event->filePath]);
            return;
        }

        Log::info('Retrieved agreement for customer.', ['customerId' => $customerId, 'agreement' => $agreement]);

        $lineCount = count($event->parsedLines);
        Log::info('Starting to price lines.', ['lineCount' => $lineCount, 'filePath' => $event->filePath]);

        foreach ($event->parsedLines as $index => $line) {
            Log::debug('Pricing line.', ['index' => $index, 'line' => $line]);
            $pricedLine = $this->pricingEngineService->priceLine($line, $agreement);
            Log::debug('Line priced.', ['index' => $index, 'pricedLine' => $pricedLine]);

            if ($index === $lineCount - 1) {
                $pricedLine['last_line'] = true;
                Log::info('Last line processed, setting last_line flag.', ['filePath' => $event->filePath]);
            }

            Event::dispatch(new PricedInvoiceLine($pricedLine, $event->filePath));
        }
        Log::info('ApplyPricing listener finished.', ['filePath' => $event->filePath, 'lines_processed' => $lineCount]);
    }
}