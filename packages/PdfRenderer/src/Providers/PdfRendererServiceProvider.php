<?php

namespace Packages\PdfRenderer\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Listeners\RenderPdf;
use Packages\PdfRenderer\Services\PdfRendererService;

class PdfRendererServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PdfRendererService::class, function ($app) {
            return new PdfRendererService();
        });
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'pdf-renderer');
    }
} 