<?php

namespace Packages\InvoiceSender\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceSender\Listeners\SendInvoice;
use Packages\InvoiceSender\Services\InvoiceSender;
use Packages\PdfRenderer\Events\PdfRendered;

class InvoiceSenderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceSender::class, function ($app) {
            return new InvoiceSender();
        });
    }
}