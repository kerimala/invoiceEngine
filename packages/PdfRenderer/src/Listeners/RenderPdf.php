<?php

namespace Packages\PdfRenderer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Events\PdfRendered;
use Packages\PdfRenderer\Services\PdfRendererService;

class RenderPdf implements ShouldQueue
{
    public function __construct(private readonly PdfRendererService $pdfRendererService)
    {
    }

    public function handle(InvoiceAssembled $event): void
    {
        $pdfPath = $this->pdfRendererService->render($event->invoiceData);
        event(new PdfRendered($pdfPath));
    }
} 