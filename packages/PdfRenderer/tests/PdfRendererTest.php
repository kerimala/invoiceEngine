<?php

namespace Packages\PdfRenderer\Tests;

use Illuminate\Support\Facades\Storage;
use Packages\PdfRenderer\Services\PdfRenderer;
use Tests\TestCase;
use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceAssembler\DTOs\InvoiceLine;
use App\Models\Agreement;

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

    public function test_renders_pdf_with_locale_formatting()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        // Create agreement with German locale
        $agreement = new Agreement([
            'currency' => 'EUR',
            'locale' => 'de',
            'customer_id' => 'test@example.com'
        ]);
        
        $invoiceData = [
            'invoice_id' => 'INV-123',
            'customer_id' => 'test@example.com',
            'lines' => [
                [
                    'description' => 'Test Product',
                    'nett_total' => 1234.56,
                    'vat_amount' => 234.87,
                    'line_total' => 1469.43,
                    'currency' => 'EUR',
                    'agreement_version' => '1.0',
                ]
            ],
            'total_amount' => 1469.43,
            'currency' => 'EUR',
        ];

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        
        // Verify the PDF was created with the correct filename
        $this->assertEquals('invoices/INV-123.pdf', $filePath);
    }

    public function test_renders_pdf_with_english_locale_formatting()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        // Create agreement with English locale
        $agreement = new Agreement([
            'currency' => 'USD',
            'locale' => 'en',
            'customer_id' => 'test@example.com'
        ]);
        
        $invoiceData = [
            'invoice_id' => 'INV-124',
            'customer_id' => 'test@example.com',
            'lines' => [
                [
                    'description' => 'Test Product',
                    'nett_total' => 1234.56,
                    'vat_amount' => 234.87,
                    'line_total' => 1469.43,
                    'currency' => 'USD',
                    'agreement_version' => '1.0',
                ]
            ],
            'total_amount' => 1469.43,
            'currency' => 'USD',
        ];

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        
        // Verify the PDF was created with the correct filename
        $this->assertEquals('invoices/INV-124.pdf', $filePath);
    }
}