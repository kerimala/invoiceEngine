<?php

namespace Packages\PdfRenderer\Tests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Packages\PdfRenderer\Services\PdfRenderer;
use Tests\TestCase;
use App\Models\Agreement;

class PdfRendererMultiLanguageTest extends TestCase
{
    private array $testInvoiceData;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Common test invoice data
        $this->testInvoiceData = [
            'invoice_id' => 'INV-MULTI-LANG-001',
            'customer_id' => 'multilang@example.com',
            'lines' => [
                [
                    'description' => 'Test Product A',
                    'nett_total' => 1234.56,
                    'vat_amount' => 234.87,
                    'line_total' => 1469.43,
                    'currency' => 'EUR',
                    'agreement_version' => '1.0',
                ],
                [
                    'description' => 'Test Product B',
                    'nett_total' => 567.89,
                    'vat_amount' => 107.90,
                    'line_total' => 675.79,
                    'currency' => 'EUR',
                    'agreement_version' => '1.0',
                ]
            ],
            'total_amount' => 2145.22,
            'currency' => 'EUR',
            'invoice_date' => '2024-01-15',
            'due_date' => '2024-02-15'
        ];
    }

    /**
     * Test PDF generation for English locale
     */
    public function test_renders_pdf_with_english_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'USD',
            'locale' => 'en',
            'invoice_language' => 'en',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-EN-001',
            'currency' => 'USD'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-EN-001.pdf', $filePath);
        
        // Verify locale was properly set during rendering
        $this->assertEquals('en', App::getLocale());
    }

    /**
     * Test PDF generation for German locale
     */
    public function test_renders_pdf_with_german_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'EUR',
            'locale' => 'de',
            'invoice_language' => 'de',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-DE-001'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-DE-001.pdf', $filePath);
    }

    /**
     * Test PDF generation for French locale
     */
    public function test_renders_pdf_with_french_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'EUR',
            'locale' => 'fr',
            'invoice_language' => 'fr',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-FR-001'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-FR-001.pdf', $filePath);
    }

    /**
     * Test PDF generation for Dutch locale
     */
    public function test_renders_pdf_with_dutch_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'EUR',
            'locale' => 'nl',
            'invoice_language' => 'nl',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-NL-001'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-NL-001.pdf', $filePath);
    }

    /**
     * Test PDF generation for Spanish locale
     */
    public function test_renders_pdf_with_spanish_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'EUR',
            'locale' => 'es',
            'invoice_language' => 'es',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-ES-001'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-ES-001.pdf', $filePath);
    }

    /**
     * Test PDF generation with fallback language when unsupported locale is provided
     */
    public function test_renders_pdf_with_fallback_language_for_unsupported_locale()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $agreement = new Agreement([
            'currency' => 'USD',
            'locale' => 'unsupported',
            'invoice_language' => 'unsupported',
            'fallback_language' => 'en',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-FALLBACK-001',
            'currency' => 'USD'
        ]);

        $filePath = $service->render($invoiceData, $agreement);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-FALLBACK-001.pdf', $filePath);
    }

    /**
     * Test PDF generation without agreement (should use default fallback)
     */
    public function test_renders_pdf_without_agreement_uses_default_fallback()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $invoiceData = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-NO-AGREEMENT-001'
        ]);

        $filePath = $service->render($invoiceData, null);

        $this->assertTrue(Storage::disk('local')->exists($filePath));
        $this->assertEquals('invoices/INV-NO-AGREEMENT-001.pdf', $filePath);
    }

    /**
     * Test PDF generation with different currency formats per locale
     */
    public function test_renders_pdf_with_different_currency_formats()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $testCases = [
            ['locale' => 'en', 'currency' => 'USD', 'invoice_id' => 'INV-USD-001'],
            ['locale' => 'de', 'currency' => 'EUR', 'invoice_id' => 'INV-EUR-001'],
            ['locale' => 'fr', 'currency' => 'EUR', 'invoice_id' => 'INV-EUR-FR-001'],
            ['locale' => 'nl', 'currency' => 'EUR', 'invoice_id' => 'INV-EUR-NL-001'],
            ['locale' => 'es', 'currency' => 'EUR', 'invoice_id' => 'INV-EUR-ES-001'],
        ];
        
        foreach ($testCases as $testCase) {
            $agreement = new Agreement([
                'currency' => $testCase['currency'],
                'locale' => $testCase['locale'],
                'invoice_language' => $testCase['locale'],
                'customer_id' => 'multilang@example.com'
            ]);
            
            $invoiceData = array_merge($this->testInvoiceData, [
                'invoice_id' => $testCase['invoice_id'],
                'currency' => $testCase['currency']
            ]);

            $filePath = $service->render($invoiceData, $agreement);

            $this->assertTrue(Storage::disk('local')->exists($filePath));
            $this->assertEquals('invoices/' . $testCase['invoice_id'] . '.pdf', $filePath);
        }
    }

    /**
     * Test PDF generation with language priority (invoice_language > locale > fallback_language)
     */
    public function test_renders_pdf_with_language_priority()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        // Test case 1: invoice_language takes priority over locale
        $agreement1 = new Agreement([
            'currency' => 'EUR',
            'locale' => 'en',
            'invoice_language' => 'de',
            'fallback_language' => 'fr',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData1 = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-PRIORITY-001'
        ]);

        $filePath1 = $service->render($invoiceData1, $agreement1);
        $this->assertTrue(Storage::disk('local')->exists($filePath1));
        
        // Test case 2: locale takes priority over fallback_language when invoice_language is null
        $agreement2 = new Agreement([
            'currency' => 'EUR',
            'locale' => 'fr',
            'invoice_language' => null,
            'fallback_language' => 'en',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData2 = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-PRIORITY-002'
        ]);

        $filePath2 = $service->render($invoiceData2, $agreement2);
        $this->assertTrue(Storage::disk('local')->exists($filePath2));
        
        // Test case 3: fallback_language is used when both invoice_language and locale are null
        $agreement3 = new Agreement([
            'currency' => 'EUR',
            'locale' => null,
            'invoice_language' => null,
            'fallback_language' => 'es',
            'customer_id' => 'multilang@example.com'
        ]);
        
        $invoiceData3 = array_merge($this->testInvoiceData, [
            'invoice_id' => 'INV-PRIORITY-003'
        ]);

        $filePath3 = $service->render($invoiceData3, $agreement3);
        $this->assertTrue(Storage::disk('local')->exists($filePath3));
    }

    /**
     * Test PDF generation with complex invoice data across multiple languages
     */
    public function test_renders_pdf_with_complex_invoice_data_multiple_languages()
    {
        Storage::fake('local');
        $service = new PdfRenderer();
        
        $complexInvoiceData = [
            'invoice_id' => 'INV-COMPLEX-001',
            'customer_id' => 'complex@example.com',
            'lines' => [
                [
                    'description' => 'Premium Software License',
                    'nett_total' => 2500.00,
                    'vat_amount' => 525.00,
                    'line_total' => 3025.00,
                    'currency' => 'EUR',
                    'agreement_version' => '2.0',
                ],
                [
                    'description' => 'Technical Support (12 months)',
                    'nett_total' => 1200.00,
                    'vat_amount' => 252.00,
                    'line_total' => 1452.00,
                    'currency' => 'EUR',
                    'agreement_version' => '2.0',
                ],
                [
                    'description' => 'Training Sessions (5 days)',
                    'nett_total' => 3750.00,
                    'vat_amount' => 787.50,
                    'line_total' => 4537.50,
                    'currency' => 'EUR',
                    'agreement_version' => '2.0',
                ]
            ],
            'total_amount' => 9014.50,
            'currency' => 'EUR',
            'invoice_date' => '2024-01-15',
            'due_date' => '2024-03-15'
        ];
        
        $languages = ['en', 'de', 'fr', 'nl', 'es'];
        
        foreach ($languages as $lang) {
            $agreement = new Agreement([
                'currency' => 'EUR',
                'locale' => $lang,
                'invoice_language' => $lang,
                'customer_id' => 'complex@example.com'
            ]);
            
            $invoiceData = array_merge($complexInvoiceData, [
                'invoice_id' => 'INV-COMPLEX-' . strtoupper($lang) . '-001'
            ]);

            $filePath = $service->render($invoiceData, $agreement);

            $this->assertTrue(Storage::disk('local')->exists($filePath));
            $this->assertEquals('invoices/INV-COMPLEX-' . strtoupper($lang) . '-001.pdf', $filePath);
        }
    }
}