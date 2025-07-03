<?php

namespace Packages\PdfRenderer\Tests;

use Illuminate\Support\Facades\Storage;
use Packages\PdfRenderer\Services\PdfRenderer;
use Tests\TestCase;
use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceAssembler\DTOs\InvoiceLine;

class PdfRendererTest extends TestCase
{
    public function test_renders_pdf_and_stores_it()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        $invoiceData = [
            'invoice_id' => 'INV-123',
            'customer_id' => 'test@example.com',
            'lines' => [
                [
                    'description' => 'Test Product',
                    'nett_total' => 80,
                    'vat_amount' => 20,
                    'line_total' => 100,
                    'currency' => 'EUR',
                    'agreement_version' => '1.0',
                ]
            ],
            'total_amount' => 100,
            'currency' => 'EUR',
        ];

        $filePath = $service->render($invoiceData);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
    }
}