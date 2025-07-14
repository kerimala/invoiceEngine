<?php

namespace Packages\InvoiceFileIngest\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;

class InvoiceFileIngestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceFileIngestService::class, function ($app) {
            return new InvoiceFileIngestService();
        });
    }

    public function boot() {}
} 