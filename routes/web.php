<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('invoice/upload', [InvoiceController::class, 'create'])->name('invoice.create');
Route::post('invoice/upload', [InvoiceController::class, 'store'])->name('invoice.store');
