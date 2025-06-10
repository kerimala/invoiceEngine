<?php

namespace Packages\InvoiceSender\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceSender\Listeners\SendInvoice;
use Packages\InvoiceSender\Services\InvoiceSenderService;
use Packages\PdfRenderer\Events\PdfRendered;

class InvoiceSenderServiceProvider extends ServiceProvider
{
    protected $listen = [
        PdfRendered::class => [
            SendInvoice::class,
        ],
    ];

    public function register()
    {
        $this->app->singleton(InvoiceSenderService::class, function ($app) {
            return new InvoiceSenderService();
        });
    }

    public function boot()
    {
    }
} 