<?php

namespace Packages\PricingEngine\tests;

use Illuminate\Support\Facades\Event;
use Packages\AgreementService\Services\AgreementService;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Packages\PricingEngine\Listeners\ApplyPricing;
use Packages\PricingEngine\Services\PricingEngineService;
use Tests\TestCase;

class ApplyPricingTest extends TestCase
{
    public function test_it_sets_last_line_flag_correctly()
    {
        Event::fake();

        $parsedLines = [
            ['line' => 1, 'Weight Charge' => 10],
            ['line' => 2, 'Weight Charge' => 20],
        ];

        $event = new CarrierInvoiceLineExtracted('test.xlsx', count($parsedLines), $parsedLines);
        
        $agreementService = new AgreementService();
        $pricingEngineService = new PricingEngineService();
        
        $listener = new ApplyPricing($pricingEngineService, $agreementService);
        $listener->handle($event);

        Event::assertDispatched(PricedInvoiceLine::class, 2);

        Event::assertDispatched(PricedInvoiceLine::class, function (PricedInvoiceLine $event) {
            return !isset($event->pricedLine['last_line']);
        });

        Event::assertDispatched(PricedInvoiceLine::class, function (PricedInvoiceLine $event) {
            return isset($event->pricedLine['last_line']) && $event->pricedLine['last_line'] === true;
        });
    }
} 