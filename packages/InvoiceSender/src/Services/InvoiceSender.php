<?php

namespace Packages\InvoiceSender\Services;

use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceSender\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

class InvoiceSender
{
    /**
     * @param Invoice $invoice
     * @param string  $pdfPath
     */
    public function send(Invoice $invoice, string $pdfPath): void
    {
        Mail::to($invoice->getCustomerEmail())
            ->send(new InvoiceMail($invoice, $pdfPath));
    }
}
