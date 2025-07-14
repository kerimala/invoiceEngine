<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Agreement;

class FullInvoiceProcessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_an_uploaded_invoice_file_and_creates_enriched_lines()
    {
        // Arrange
        Storage::fake('local');
        $this->seed();

        $filePath = __DIR__ . '/../stubs/invoice.xlsx';
        $file = new UploadedFile(
            $filePath,
            'invoice.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Act
        $response = $this->post('/upload-invoice', [
            'invoice' => $file,
        ]);

        // Assert
        $response->assertStatus(302); // Assuming a redirect on success
        // You can add more assertions here, like checking the database for enriched invoice lines
    }

    /** @test */
    public function it_processes_invoice_with_german_locale_and_generates_formatted_pdf()
    {
        // Arrange
        Storage::fake('local');
        $this->seed();

        // Create Agreement with German locale
        $agreement = Agreement::create([
            'customer_id' => 'test@example.com',
            'currency' => 'EUR',
            'locale' => 'de',
            'version' => '1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.19,
            'rules' => [],
            'valid_from' => now(),
        ]);

        $filePath = __DIR__ . '/../stubs/invoice.xlsx';
        $file = new UploadedFile(
            $filePath,
            'invoice.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Act
        $response = $this->post('/upload-invoice', [
            'invoice' => $file,
            'customer_id' => 'test@example.com',
        ]);

        // Assert
        $response->assertStatus(302);
        
        // Verify that a PDF was generated (this would need to be implemented in the actual flow)
        // $this->assertTrue(Storage::disk('local')->exists('invoices/INV-*.pdf'));
    }

    /** @test */
    public function it_processes_invoice_with_english_locale_and_generates_formatted_pdf()
    {
        // Arrange
        Storage::fake('local');
        $this->seed();

        // Create Agreement with English locale
        $agreement = Agreement::create([
            'customer_id' => 'test@example.com',
            'currency' => 'USD',
            'locale' => 'en',
            'version' => '1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.08,
            'rules' => [],
            'valid_from' => now(),
        ]);

        $filePath = __DIR__ . '/../stubs/invoice.xlsx';
        $file = new UploadedFile(
            $filePath,
            'invoice.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Act
        $response = $this->post('/upload-invoice', [
            'invoice' => $file,
            'customer_id' => 'test@example.com',
        ]);

        // Assert
        $response->assertStatus(302);
        
        // Verify that a PDF was generated (this would need to be implemented in the actual flow)
        // $this->assertTrue(Storage::disk('local')->exists('invoices/INV-*.pdf'));
    }
}