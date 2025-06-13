<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function create()
    {
        return view('invoice.create');
    }

    public function store(Request $request, InvoiceFileIngestService $ingestService)
    {
        $request->validate([
            'invoice_file' => 'required|file',
        ]);

        $file = $request->file('invoice_file');

        $path = $file->store('invoices');

        $ingestService->ingest(Storage::path($path));

        return 'File uploaded and is being processed.';
    }
}
