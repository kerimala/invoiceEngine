<?php

namespace Packages\PdfRenderer\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use Packages\InvoiceAssembler\DTOs\Invoice;
use InvoicingEngine\UnitConverter\Services\FormattingService;
use InvoicingEngine\UnitConverter\Services\UnitConverterService;
use App\Models\Agreement;

class PdfRenderer
{
    private FormattingService $formattingService;

    public function __construct()
    {
        $this->formattingService = new FormattingService(new UnitConverterService());
    }
    /**
     * Render invoice data to a PDF and store it.
     *
     * @param array|Invoice $invoiceData
     * @param Agreement|null $agreement
     * @return string Path to stored PDF
     */
    public function render(array $invoiceData, ?Agreement $agreement = null): string
    {
        $invoiceId = $invoiceData['invoice_id'] ?? 'unknown_invoice';
        Log::info('Starting PDF rendering.', ['invoice_id' => $invoiceId]);

        try {
            // Ensure the view file exists
            if (!View::exists('pdf-renderer::invoice')) {
                Log::error('PDF template view not found: pdf-renderer::invoice');
                throw new \Exception('PDF template not found.');
            }

            // Render the HTML from a Blade view
            $html = View::make('pdf-renderer::invoice', [
                'invoice' => $invoiceData,
                'agreement' => $agreement,
                'formatter' => $this->formattingService
            ])->render();
            Log::debug('Rendered HTML from Blade template.', ['invoice_id' => $invoiceId]);

            // Create a new Dompdf instance
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('tempDir', storage_path('app/temp')); // ensure temp dir exists
            $dompdf = new Dompdf($options);

            // Increase PHP limits for large documents
            @ini_set('memory_limit', '1024M');
            @set_time_limit(0);

            $dompdf->loadHtml($html);

            // (Optional) Setup the paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the HTML as PDF
            $dompdf->render();
            Log::debug('Dompdf rendered the HTML to PDF.', ['invoice_id' => $invoiceId]);

            // Get the PDF output
            $pdfOutput = $dompdf->output();
            
            // Define a filename
            $filename = 'invoices/' . $invoiceId . '.pdf';

            // Ensure directory exists
            if (!Storage::disk('local')->exists('invoices')) {
                Storage::disk('local')->makeDirectory('invoices');
            }
            
            // Store the file
            Storage::disk('local')->put($filename, $pdfOutput);
            $fullPath = Storage::disk('local')->path($filename);
            Log::info('PDF successfully rendered and stored.', ['invoice_id' => $invoiceId, 'path' => $fullPath]);
            
            return $filename;

        } catch (\Throwable $th) {
            Log::error('Error during PDF rendering.', [
                'invoice_id' => $invoiceId, 
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            // Re-throw the exception to be handled by the queue worker
            throw $th;
        }
    }
}
