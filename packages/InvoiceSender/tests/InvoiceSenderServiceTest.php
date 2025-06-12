<?php

namespace Packages\InvoiceSender\Tests;

use Illuminate\Support\Facades\Log;
use Packages\InvoiceSender\Services\InvoiceSenderService;
use Tests\TestCase;

class InvoiceSenderServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = new InvoiceSenderService();
        $this->assertInstanceOf(InvoiceSenderService::class, $service);
    }

    public function test_logs_pdf_path()
    {
        Log::spy();
        $service = new InvoiceSenderService();
        $pdfPath = 'path/to/invoice.pdf';

        $service->send($pdfPath);

        Log::shouldHaveReceived('info')->once()->with("Sending invoice: {$pdfPath}");
    }
} 