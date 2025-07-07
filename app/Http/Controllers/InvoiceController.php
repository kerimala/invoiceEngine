<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;
use Illuminate\Support\Facades\Storage;
use Packages\InvoiceService\Services\InvoiceService;
use App\Models\Agreement;

class InvoiceController extends Controller
{
    public function create()
    {
        $agreements = Agreement::all();
        return view('invoice.create', ['agreements' => $agreements]);
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

    public function generate(Request $request, InvoiceService $invoiceService)
    {
        $request->validate([
            'agreement_id' => 'required|exists:agreements,id',
        ]);

        $agreement = Agreement::find($request->input('agreement_id'));

        $invoice = $invoiceService->generateInvoiceForAgreement($agreement);

        return response()->json($invoice);
    }
}
