<?php

namespace Packages\InvoiceAssembler\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceAssembler\Listeners\AssembleInvoice;
use Packages\InvoiceAssembler\Services\InvoiceAssemblerService;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;

class InvoiceAssemblerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceAssemblerService::class, function ($app) {
            return new InvoiceAssemblerService();
        });

        Event::listen(PricedInvoiceLine::class, AssembleInvoice::class);
    }
} 