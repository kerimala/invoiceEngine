<?php

namespace Packages\InvoiceSender\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceSender\Events\InvoiceSent;
use Packages\InvoiceSender\Services\InvoiceSenderService;
use Packages\PdfRenderer\Events\PdfRendered;

class SendInvoice implements ShouldQueue
{
    public function __construct(private readonly InvoiceSenderService $invoiceSenderService)
    {
    }

    public function handle(PdfRendered $event): void
    {
        $this->invoiceSenderService->send($event->pdfPath);
        event(new InvoiceSent($event->pdfPath));
    }
} 