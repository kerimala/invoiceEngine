<?php

namespace Packages\InvoiceAssembler\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceAssembler\Services\InvoiceAssemblerService;

class InvoiceAssemblerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceAssemblerService::class, function ($app) {
            return new InvoiceAssemblerService();
        });

        // Event listener is registered in EventServiceProvider
        // Removed duplicate registration
    }
}