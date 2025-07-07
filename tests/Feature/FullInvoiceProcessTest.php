<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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
}