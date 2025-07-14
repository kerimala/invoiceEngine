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
