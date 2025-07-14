<?php

namespace Packages\InvoiceParser\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceParser\Services\InvoiceParserService;

class InvoiceParserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceParserService::class, function ($app) {
            return new InvoiceParserService();
        });

        // Event listener is registered in EventServiceProvider
        // Removed duplicate registration to fix test failures
    }
}