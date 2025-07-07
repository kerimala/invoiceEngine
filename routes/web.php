<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AgreementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('invoice/upload', [InvoiceController::class, 'create'])->name('invoice.create');
Route::post('invoice/upload', [InvoiceController::class, 'store'])->name('invoice.store');
Route::post('invoice/generate', [InvoiceController::class, 'generate'])->name('invoice.generate');
Route::post('agreement/store', [AgreementController::class, 'store'])->name('agreement.store');
