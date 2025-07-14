<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;

class InvoiceUploadController extends Controller
{
    public function __construct(private readonly InvoiceFileIngestService $ingestService)
    {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:xlsx,csv,xml,txt',
        ]);

        $this->ingestService->ingest($request->file('invoice')->getRealPath());

        return back()->with('success', 'Invoice uploaded successfully!');
    }
}