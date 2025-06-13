<?php

namespace Packages\PdfRenderer\Services;

use Packages\PdfRenderer\Facades\Pdf;
use Packages\PdfRenderer\Facades\PdfDocument;
use Illuminate\Support\Facades\Storage;
use Packages\InvoiceAssembler\DTOs\Invoice;

class PdfRenderer
{
    /**
     * Render an Invoice DTO to PDF and store it.
     *
     * @param Invoice $invoice
     * @return string Path to stored PDF
     */
    public function render(Invoice $invoice): string
    {
        try {
            $pdfDoc = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

            $filename = 'invoices/' . $invoice->getId() . '.pdf';


            Storage::disk('local')->put($filename, $pdfDoc->output());


            return $filename;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
