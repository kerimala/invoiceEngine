<?php

namespace Packages\InvoiceSender\Mail;

use Packages\InvoiceAssembler\DTOs\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public string  $pdfPath;

    public function __construct(Invoice $invoice, string $pdfPath)
    {
        $this->invoice = $invoice;
        $this->pdfPath  = $pdfPath;
        $this->build();
    }

    /**
     * Build the message.
     */
    public function build(): Mailable
    {

        return $this
            ->subject('Invoice #' . $this->invoice->getInvoiceId())
            ->view('emails.invoice')
            ->with(['invoice' => $this->invoice])
            ->attach(
                storage_path('app/' . $this->pdfPath),
                [
                    'as'   => 'invoice.pdf',
                    'mime' => 'application/pdf',
                ]
            );
    }

    /**
     * Expose attachments() for tests.
     */
    public function attachments(): array
    {
        return $this->attachments;
    }
}
