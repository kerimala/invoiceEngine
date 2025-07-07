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
        // Debug: Log the type and value of pdfPath
        \Illuminate\Support\Facades\Log::info('SendInvoice: pdfPath debug', [
            'pdfPath_type' => gettype($event->pdfPath),
            'pdfPath_value' => $event->pdfPath,
            'is_string' => is_string($event->pdfPath),
            'is_array' => is_array($event->pdfPath)
        ]);
        
        // Reconstruct Invoice DTO from array data
        $invoice = new Invoice($event->invoiceData['invoice_id'], $event->invoiceData['customer_email']);
        $invoice->setTotalAmount($event->invoiceData['total_amount']);
        $invoice->setCurrency($event->invoiceData['currency']);
        $invoice->setFilePath($event->invoiceData['file_path']);
        
        // Reconstruct invoice lines
        $lines = [];
        foreach ($event->invoiceData['lines'] as $lineData) {
            $line = new InvoiceLine(
                $lineData['description'],
                (float) $lineData['quantity'],
                (float) $lineData['unit_price'],
                (float) $lineData['nett_total'],
                (float) $lineData['vat_amount'],
                $lineData['product_name'] ?? null,
                $lineData['currency'] ?? 'USD',
                $lineData['agreement_version'] ?? '1.0',
                (bool) ($lineData['last_line'] ?? false)
            );
            $lines[] = $line;
        }
        $invoice->setLines($lines);
        
        $this->invoiceSender->send($invoice, $event->pdfPath);
        event(new InvoiceSent($event->pdfPath));
    }
}