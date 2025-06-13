<?php

namespace Packages\InvoiceParser\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Listeners\ParseInvoiceFile;
use Packages\InvoiceParser\Services\InvoiceParserService;

class InvoiceParserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(InvoiceParserService::class, function ($app) {
            return new InvoiceParserService();
        });
    }
} 