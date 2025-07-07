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
            ['product_name' => 'Test Product', 'quantity' => 1, 'unit_price' => 100, 'total' => 100],
            ['product_name' => 'Another Product', 'quantity' => 2, 'unit_price' => 50, 'total' => 100],
        ];

        $invoice = $service->createInvoice($lines);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertCount(2, $invoice->getLines());
        $this->assertEquals('Test Product', $invoice->getLines()[0]->getDescription());
    }
} 