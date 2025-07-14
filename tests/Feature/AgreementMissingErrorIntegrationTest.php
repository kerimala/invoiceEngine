<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Packages\AgreementService\Services\AgreementService;
use Packages\AgreementService\Exceptions\AgreementMissingException;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Listeners\ApplyPricing;
use Illuminate\Support\Facades\Log;

class AgreementMissingErrorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_422_error_when_processing_invoice_with_no_agreement(): void
    {
        // Arrange - simulate an invoice processing scenario with no agreements in database
        $parsedLines = [
            [
                'Billing Account' => 'CUST-404',
                'Invoice Number' => 'INV-2025-001',
                'Weight Charge' => 25.50,
                'item' => 'Package Delivery',
                'quantity' => 1
            ],
        ];

        $event = new CarrierInvoiceLineExtracted('invoice-404.xlsx', count($parsedLines), $parsedLines);
        $listener = $this->app->make(ApplyPricing::class);

        // Act & Assert
        try {
            $listener->handle($event);
            $this->fail('Expected AgreementMissingException was not thrown');
        } catch (AgreementMissingException $e) {
            // Verify error code 422
            $this->assertEquals(422, $e->getCode());
            
            // Verify customer ID and invoice ID are included
            $this->assertEquals('CUST-404', $e->getCustomerId());
            $this->assertEquals('INV-2025-001', $e->getInvoiceId());
            
            // Verify error message format
            $expectedMessage = "Agreement Missing: No valid agreement found for customer 'CUST-404' (Invoice ID: INV-2025-001)";
            $this->assertEquals($expectedMessage, $e->getMessage());
            
            // Verify the error is logged with timestamp and marked as blocking
            // Note: In a real scenario, you might want to check the actual log entries
        }
    }

    /** @test */
    public function it_logs_error_with_required_information(): void
    {
        // Arrange
        Log::spy();
        $agreementService = new AgreementService();
        
        // Act & Assert
        try {
            $agreementService->getAgreementForCustomer('CUST-MISSING');
            $this->fail('Expected AgreementMissingException was not thrown');
        } catch (AgreementMissingException $e) {
            // Verify that error is logged with required information
            Log::shouldHaveReceived('error')
                ->with(
                    'No agreement found for customer (neither custom nor standard).',
                    \Mockery::on(function ($context) {
                        return isset($context['customerId']) &&
                               isset($context['timestamp']) &&
                               isset($context['blocking']) &&
                               $context['customerId'] === 'CUST-MISSING' &&
                               $context['blocking'] === true;
                    })
                );
        }
    }

    /** @test */
    public function it_prevents_invoice_line_processing_when_no_agreement_found(): void
    {
        // Arrange
        $parsedLines = [
            [
                'Billing Account' => 'NO-AGREEMENT-CUSTOMER',
                'Invoice Number' => 'INV-BLOCKED',
                'Weight Charge' => 100.00
            ],
        ];

        $event = new CarrierInvoiceLineExtracted('blocked-invoice.xlsx', count($parsedLines), $parsedLines);
        $listener = $this->app->make(ApplyPricing::class);

        // Act & Assert
        $this->expectException(AgreementMissingException::class);
        $this->expectExceptionCode(422);
        
        // This should throw an exception and prevent any further invoice line processing
        $listener->handle($event);
        
        // If we reach this point, the test should fail because the exception should have been thrown
        $this->fail('Invoice processing should have been blocked due to missing agreement');
    }
}