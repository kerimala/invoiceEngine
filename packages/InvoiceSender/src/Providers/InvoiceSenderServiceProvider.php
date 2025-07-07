<?php

namespace Packages\InvoiceSender\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceSender\Listeners\SendInvoice;
use Packages\InvoiceSender\Services\InvoiceSenderService;
use Packages\PdfRenderer\Events\PdfRendered;

class InvoiceSenderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceSenderService::class, function ($app) {
            return new InvoiceSenderService();
        });
    }
} 