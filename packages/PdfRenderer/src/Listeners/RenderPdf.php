<?php

namespace Packages\PdfRenderer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Events\PdfRendered;
use Packages\PdfRenderer\Services\PdfRenderer;

class RenderPdf implements ShouldQueue
{
    public function __construct(private readonly PdfRenderer $pdfRenderer)
    {
    }

    public function handle(InvoiceAssembled $event): void
    {
        $pdfPath = $this->pdfRenderer->render($event->invoiceData);
        event(new PdfRendered($pdfPath, $event->invoiceData));
    }
}