<?php

namespace Packages\InvoiceSender\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceAssembler\DTOs\InvoiceLine;
use Packages\InvoiceSender\Events\InvoiceSent;
use Packages\InvoiceSender\Services\InvoiceSender;
use Packages\PdfRenderer\Events\PdfRendered;

class SendInvoice implements ShouldQueue
{
    public function __construct(private readonly InvoiceSender $invoiceSender)
    {
    }

    public function handle(PdfRendered $event): void
    {
        // Reconstruct Invoice DTO from array data
        $invoice = new Invoice($event->invoiceData['invoice_id'], $event->invoiceData['customer_id']);
        $invoice->setTotalAmount($event->invoiceData['total_amount']);
        $invoice->setCurrency($event->invoiceData['currency']);
        $invoice->setFilePath($event->invoiceData['file_path']);
        
        // Reconstruct invoice lines
        $lines = [];
        foreach ($event->invoiceData['lines'] as $lineData) {
            $line = new InvoiceLine(
                $lineData['description'],
                $lineData['quantity'],
                $lineData['unit_price'],
                $lineData['currency'],
                $lineData['agreement_version'],
                $lineData['last_line'],
                $lineData['nett_total'],
                $lineData['vat_amount']
            );
            if (isset($lineData['product_name'])) {
                $line->setProductName($lineData['product_name']);
            }
            $lines[] = $line;
        }
        $invoice->setLines($lines);
        
        $this->invoiceSender->send($invoice, $event->pdfPath);
        event(new InvoiceSent($event->pdfPath));
    }
}