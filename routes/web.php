<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AgreementController;

use App\Http\Controllers\DatabaseViewController;

use App\Http\Controllers\InvoiceUploadController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/upload-invoice', InvoiceUploadController::class);

Route::get('/database', [DatabaseViewController::class, 'index']);

Route::get('invoice/upload', [InvoiceController::class, 'create'])->name('invoice.create');
Route::post('invoice/upload', [InvoiceController::class, 'store'])->name('invoice.store');
Route::post('invoice/generate', [InvoiceController::class, 'generate'])->name('invoice.generate');
Route::get('agreements', [AgreementController::class, 'index'])->name('agreements.index');
Route::post('agreement/store', [AgreementController::class, 'store'])->name('agreement.store');
Route::delete('agreement/{agreement}', [AgreementController::class, 'destroy'])->name('agreement.destroy');

// Test route for PDF rendering
Route::get('/test-pdf', function () {
    $pdfRenderer = new \Packages\PdfRenderer\Services\PdfRenderer();
    
    // Create a test agreement with made-up company info
    $agreement = new \App\Models\Agreement([
        'currency' => 'EUR',
        'locale' => 'nl',
        'customer_id' => 'test-customer@example.com',
        'invoicing_company_name' => 'Demo Logistics BV',
        'invoicing_company_address' => "Demostraat 123\n1234 AB Voorbeeldstad\nNederland",
        'invoicing_company_phone' => '+31 20 555 0123',
        'invoicing_company_email' => 'info@demo-logistics.example',
        'invoicing_company_website' => 'www.demo-logistics.example',
        'invoicing_company_vat_number' => 'NL123456789B01',
        'logo_path' => null, // No logo for test
        'invoice_number_prefix' => 'DEMO-',
        'invoice_footer_text' => 'Dit is een demo factuur voor test doeleinden.'
    ]);
    
    $invoiceData = [
        'invoice_id' => 'TEST-12345',
        'invoice_number' => '12345',
        'customer_id' => 'CUST-TEST',
        'customer_name' => 'Demo Klant B.V.',
        'customer_address' => "Jan de Vries\nVoorbeeldstraat 456\n9876 ZX Teststad\nNederland",
        'customer_vat_number' => 'NL987654321B01',
        'invoice_date' => date('d-m-Y'),
        'lines' => [
            [
                'description' => 'Transport diensten (incl. 21% BTW)',
                'nett_total' => 1500.00,
                'vat_amount' => 315.00,
                'line_total' => 1815.00,
                'currency' => 'EUR',
                'agreement_version' => '1.0',
            ],
            [
                'description' => 'Administratiekosten (incl. 21% BTW)',
                'nett_total' => 250.00,
                'vat_amount' => 52.50,
                'line_total' => 302.50,
                'currency' => 'EUR',
                'agreement_version' => '1.0',
            ]
        ],
        'subtotal' => 1750.00,
        'vat_total' => 367.50,
        'total_amount' => 2117.50,
        'currency' => 'EUR',
    ];
    
    $filePath = $pdfRenderer->render($invoiceData, $agreement);
    
    return response()->file(storage_path('app/' . $filePath));
})->name('test.pdf');
