<?php

namespace Packages\InvoiceSender\Tests;

use Illuminate\Support\Facades\Mail;
use Packages\InvoiceSender\Mail\InvoiceMail;
use Packages\InvoiceSender\Services\InvoiceSender;
use Tests\TestCase;
use Packages\InvoiceAssembler\DTOs\Invoice;

class InvoiceSenderTest extends TestCase
{
    public function test_sends_invoice_email()
    {
        Mail::fake();

        $service = new InvoiceSender();
        $invoice = new Invoice('INV-123', 'test@example.com');
        $pdfPath = 'path/to/invoice.pdf';

        $service->send($invoice, $pdfPath);

        Mail::assertSent(InvoiceMail::class, function ($mail) use ($invoice, $pdfPath) {
            return $mail->hasTo($invoice->getCustomerEmail()) && $mail->pdfPath === $pdfPath;
        });
    }
} 