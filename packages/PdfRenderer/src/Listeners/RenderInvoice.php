<?php

namespace Packages\PdfRenderer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Services\PdfRenderer;

class RenderInvoice implements ShouldQueue
{
    public function __construct(private readonly PdfRenderer $pdfRenderer)
    {
    }

    public function handle(InvoiceAssembled $event): void
    {
        Log::info('RenderInvoice listener received InvoiceAssembled event.', ['invoice_id' => $event->invoiceData['invoice_id']]);
        try {
            $this->pdfRenderer->render($event->invoiceData);
        } catch (\Throwable $e) {
            Log::critical('PDF rendering failed and exception was caught in the listener.', [
                'invoice_id' => $event->invoiceData['invoice_id'],
                'error' => $e->getMessage()
            ]);
            // Optionally, re-throw or handle the failure (e.g., dispatch a failed event)
        }
    }
} 