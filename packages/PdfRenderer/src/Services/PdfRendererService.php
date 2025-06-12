<?php

namespace Packages\PdfRenderer\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class PdfRendererService
{
    public function render(array $invoiceData): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'invoice') . '.html';
        Pdf::view('pdf-renderer::invoice', ['invoiceData' => $invoiceData])->save($tempPath);

        $fileName = 'invoice-' . time() . '.pdf';
        Storage::disk('local')->put('invoices/' . $fileName, file_get_contents($tempPath));
        unlink($tempPath);

        return Storage::disk('local')->path('invoices/' . $fileName);
    }
} 