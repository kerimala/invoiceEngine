<?php

namespace Packages\InvoiceAssembler\Tests;

use Packages\InvoiceAssembler\Services\InvoiceAssembler;
use Packages\InvoiceAssembler\DTOs\Invoice;
use Tests\TestCase;

class InvoiceAssemblerTest extends TestCase
{
    public function test_can_create_invoice()
    {
        $service = new InvoiceAssembler();
        $lines = [
            [
                'Product Name' => 'Test Product',
                'description' => 'Test Product',
                'nett_total' => 80,
                'vat_amount' => 20,
                'line_total' => 100,
                'currency' => 'EUR',
                'agreement_version' => '1.0',
                'last_line' => false,
            ],
            [
                'Product Name' => 'Another Product',
                'description' => 'Another Product',
                'nett_total' => 80,
                'vat_amount' => 20,
                'line_total' => 100,
                'currency' => 'EUR',
                'agreement_version' => '1.0',
                'last_line' => true,
            ],
        ];

        $invoice = $service->createInvoice($lines);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertCount(2, $invoice->getLines());
        $this->assertEquals('Test Product', $invoice->getLines()[0]->getDescription());
    }
}