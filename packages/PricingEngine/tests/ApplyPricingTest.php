<?php

namespace InvoicingEngine\PricingEngine\Tests;

use Packages\AgreementService\Services\AgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Events\PricedInvoiceLine;
use InvoicingEngine\PricingEngine\Listeners\ApplyPricing;
use Packages\AgreementService\Exceptions\AgreementMissingException;
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

    public function test_it_throws_agreement_missing_exception_when_no_agreement_found()
    {
        // Arrange - no agreements in database
        $parsedLines = [
            [
                'Billing Account' => 'non-existent-customer',
                'Invoice Number' => 'INV-12345',
                'Weight Charge' => 10
            ],
        ];

        $event = new CarrierInvoiceLineExtracted('test.xlsx', count($parsedLines), $parsedLines);
        $listener = $this->app->make(ApplyPricing::class);

        // Act & Assert
        $this->expectException(AgreementMissingException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage("Agreement Missing: No valid agreement found for customer 'non-existent-customer' (Invoice ID: INV-12345)");

        $listener->handle($event);
    }

    public function test_it_throws_agreement_missing_exception_without_invoice_id_when_not_available()
    {
        // Arrange - no agreements in database, no invoice number in parsed lines
        $parsedLines = [
            [
                'Billing Account' => 'non-existent-customer',
                'Weight Charge' => 10
            ],
        ];

        $event = new CarrierInvoiceLineExtracted('test.xlsx', count($parsedLines), $parsedLines);
        $listener = $this->app->make(ApplyPricing::class);

        // Act & Assert
        $this->expectException(AgreementMissingException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage("Agreement Missing: No valid agreement found for customer 'non-existent-customer'");

        $listener->handle($event);
    }

    public function test_agreement_missing_exception_includes_customer_and_invoice_ids()
    {
        // Arrange
        $customerId = 'test-customer';
        $invoiceId = 'INV-98765';
        $parsedLines = [
            [
                'Billing Account' => $customerId,
                'Invoice Number' => $invoiceId,
                'Weight Charge' => 15
            ],
        ];

        $event = new CarrierInvoiceLineExtracted('test.xlsx', count($parsedLines), $parsedLines);
        $listener = $this->app->make(ApplyPricing::class);

        try {
            // Act
            $listener->handle($event);
            $this->fail('Expected AgreementMissingException was not thrown');
        } catch (AgreementMissingException $e) {
            // Assert
            $this->assertEquals($customerId, $e->getCustomerId());
            $this->assertEquals($invoiceId, $e->getInvoiceId());
            $this->assertEquals(422, $e->getCode());
            $this->assertStringContainsString($customerId, $e->getMessage());
            $this->assertStringContainsString($invoiceId, $e->getMessage());
        }
    }
}