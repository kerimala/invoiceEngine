<?php

namespace Packages\PdfRenderer\Tests;

use Illuminate\Support\Facades\Storage;
use Packages\PdfRenderer\Services\PdfRendererService;
use Tests\TestCase;

class PdfRendererServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = new PdfRendererService();
        $this->assertInstanceOf(PdfRendererService::class, $service);
    }

    public function test_renders_pdf_and_stores_it()
    {
        Storage::fake('local');
        $service = new PdfRendererService();
        $invoiceData = [
            'filePath' => 'path/to/invoice.csv',
            'lines' => [
                ['item' => 'test1', 'price' => 100],
                ['item' => 'test2', 'price' => 200],
            ],
        ];

        $pdfPath = $service->render($invoiceData);

        Storage::disk('local')->assertExists('invoices/' . basename($pdfPath));
    }
} 