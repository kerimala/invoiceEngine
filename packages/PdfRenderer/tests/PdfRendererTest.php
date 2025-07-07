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
        $lines = [
            new InvoiceLine('Test Product', 1, 100),
        ];
        $invoice = new Invoice('INV-123', 'test@example.com');
        $invoice->setLines($lines);
        $invoice->setTotalAmount(100);


        $filePath = $service->render($invoice);

        Storage::disk('local')->assertExists($filePath);
    }
} 