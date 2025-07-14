<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\EnrichedInvoiceLine;
use Illuminate\Http\Request;

class DatabaseViewController extends Controller
{
    public function index()
    {
        $agreements = Agreement::all();
        $enrichedInvoiceLines = EnrichedInvoiceLine::all();

        return view('database.index', compact('agreements', 'enrichedInvoiceLines'));
    }
    //
}
