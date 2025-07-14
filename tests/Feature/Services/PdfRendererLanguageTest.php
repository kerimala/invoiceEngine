<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\PdfRenderer\Services\PdfRenderer;
use App\Models\Agreement;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class PdfRendererLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function it_renders_pdf_with_english_locale()
    {
        // Arrange
        $agreement = Agreement::create([
            'customer_id' => 'TEST001',
            'version' => '1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'locale' => 'en',
            'invoice_language' => 'en',
            'fallback_language' => 'en',
            'rules' => []
        ]);

        $invoiceData = [
            'invoice_id' => 'INV-001',
            'customer_id' => 'TEST001',
            'customer_name' => 'Test Customer Ltd.',
            'total_amount' => 121.00,
            'vat_total' => 21.00,
            'lines' => [
                [
                    'description' => 'Test Service',
                    'nett_total' => 100.00,
                    'vat_amount' => 21.00,
                    'line_total' => 121.00
                ]
            ]
        ];

        $renderer = new PdfRenderer();

        // Act
        $result = $renderer->render($invoiceData, $agreement);

        // Assert
        $this->assertNotNull($result);
        $this->assertStringContainsString('invoices/', $result);
        $this->assertTrue(Storage::disk('local')->exists($result));
    }

    /** @test */
    public function it_renders_pdf_with_german_locale()
    {
        // Arrange
        $agreement = Agreement::create([
            'customer_id' => 'TEST002',
            'version' => '1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.19,
            'currency' => 'EUR',
            'locale' => 'de',
            'invoice_language' => 'de',
            'fallback_language' => 'en',
            'rules' => []
        ]);

        $invoiceData = [
            'invoice_id' => 'INV-002',
            'customer_id' => 'TEST002',
            'customer_name' => 'Test Kunde GmbH',
            'total_amount' => 119.00,
            'vat_total' => 19.00,
            'lines' => [
                [
                    'description' => 'Test Dienstleistung',
                    'nett_total' => 100.00,
                    'vat_amount' => 19.00,
                    'line_total' => 119.00
                ]
            ]
        ];

        $renderer = new PdfRenderer();

        // Act
        $result = $renderer->render($invoiceData, $agreement);

        // Assert
        $this->assertNotNull($result);
        $this->assertStringContainsString('invoices/', $result);
        $this->assertTrue(Storage::disk('local')->exists($result));
    }

    /** @test */
    public function it_uses_fallback_language_when_invoice_language_not_set()
    {
        // Arrange
        $agreement = Agreement::create([
            'customer_id' => 'TEST003',
            'version' => '1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'locale' => 'fr',
            'invoice_language' => null,
            'fallback_language' => 'en',
            'rules' => []
        ]);

        $invoiceData = [
            'invoice_id' => 'INV-003',
            'customer_id' => 'TEST003',
            'customer_name' => 'Test Client SARL',
            'total_amount' => 121.00,
            'vat_total' => 21.00,
            'lines' => [
                [
                    'description' => 'Service de test',
                    'nett_total' => 100.00,
                    'vat_amount' => 21.00,
                    'line_total' => 121.00
                ]
            ]
        ];

        $renderer = new PdfRenderer();

        // Act
        $result = $renderer->render($invoiceData, $agreement);

        // Assert
        $this->assertNotNull($result);
        $this->assertStringContainsString('invoices/', $result);
        $this->assertTrue(Storage::disk('local')->exists($result));
    }

    /** @test */
    public function it_renders_pdf_without_agreement_using_default_locale()
    {
        // Arrange
        $invoiceData = [
            'invoice_id' => 'INV-004',
            'customer_id' => 'TEST004',
            'customer_name' => 'Test Customer',
            'total_amount' => 121.00,
            'vat_total' => 21.00,
            'lines' => [
                [
                    'description' => 'Test Service',
                    'nett_total' => 100.00,
                    'vat_amount' => 21.00,
                    'line_total' => 121.00
                ]
            ]
        ];

        $renderer = new PdfRenderer();

        // Act
        $result = $renderer->render($invoiceData);

        // Assert
        $this->assertNotNull($result);
        $this->assertStringContainsString('invoices/', $result);
        $this->assertTrue(Storage::disk('local')->exists($result));
    }
}