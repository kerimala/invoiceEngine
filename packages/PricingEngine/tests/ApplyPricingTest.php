<?php

namespace InvoicingEngine\PricingEngine\Tests;

use Packages\AgreementService\Services\AgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Events\PricedInvoiceLine;
use InvoicingEngine\PricingEngine\Listeners\ApplyPricing;
use App\Models\Agreement;
use Tests\TestCase;

class ApplyPricingTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_sets_last_line_flag_correctly()
    {
        $agreementService = $this->app->make(AgreementService::class);
        $agreementService->createNewVersion('some_customer_id', [
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 21.0,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'SUR',
                'surcharge_suffix' => 'CHARGE',
            ],
        ]);

        Event::fake();

        $parsedLines = [
            ['line' => 1, 'Weight Charge' => 10],
            ['line' => 2, 'Weight Charge' => 20],
        ];

        $event = new CarrierInvoiceLineExtracted('test.xlsx', count($parsedLines), $parsedLines);

        $listener = $this->app->make(ApplyPricing::class);
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