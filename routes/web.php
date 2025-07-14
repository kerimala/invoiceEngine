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

// Test route for PDF rendering with dynamic language support
Route::get('/test-pdf', function (\Illuminate\Http\Request $request) {
    $pdfRenderer = new \Packages\PdfRenderer\Services\PdfRenderer();
    
    // Get language from query parameter, default to 'nl'
    $locale = $request->get('lang', 'nl');
    
    // Set currency based on locale
    $currency = match($locale) {
        'en' => 'USD',
        'de' => 'EUR',
        'es' => 'EUR',
        'fr' => 'EUR',
        'nl' => 'EUR',
        default => 'EUR'
    };
    
    // Create a test agreement with dynamic locale
    $agreement = new \App\Models\Agreement([
        'currency' => $currency,
        'locale' => $locale,
        'invoice_language' => $locale,
        'fallback_language' => 'en',
        'customer_id' => 'test-customer@example.com',
        'invoicing_company_name' => 'Demo Logistics BV',
        'invoicing_company_address' => "Demostraat 123\n1234 AB Voorbeeldstad\nNederland",
        'invoicing_company_phone' => '+31 20 555 0123',
        'invoicing_company_email' => 'info@demo-logistics.example',
        'invoicing_company_website' => 'www.demo-logistics.example',
        'invoicing_company_vat_number' => 'NL123456789B01',
        'logo_path' => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/Logo-Test.png'))), // Logo for test
        'invoice_number_prefix' => 'DEMO-',
        'invoice_footer_text' => 'Dit is een demo factuur voor test doeleinden.'
    ]);
    
    // Set Laravel's app locale for translations
    app()->setLocale($locale);
    
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
                'description' => __('invoice.transport_services'),
                'nett_total' => 1500.00,
                'vat_amount' => 315.00,
                'line_total' => 1815.00,
                'currency' => $currency,
                'agreement_version' => '1.0',
            ],
            [
                'description' => __('invoice.administrative_costs'),
                'nett_total' => 250.00,
                'vat_amount' => 52.50,
                'line_total' => 302.50,
                'currency' => $currency,
                'agreement_version' => '1.0',
            ]
        ],
        'subtotal' => 1750.00,
        'vat_total' => 367.50,
        'total_amount' => 2117.50,
        'currency' => $currency,
    ];
    
    $filePath = $pdfRenderer->render($invoiceData, $agreement);
    
    return response()->file(storage_path('app/' . $filePath));
})->name('test.pdf');
// Usage examples:
// /test-pdf?lang=en (English with USD)
// /test-pdf?lang=de (German with EUR)
// /test-pdf?lang=es (Spanish with EUR)
// /test-pdf?lang=fr (French with EUR)
// /test-pdf?lang=nl (Dutch with EUR - default)
