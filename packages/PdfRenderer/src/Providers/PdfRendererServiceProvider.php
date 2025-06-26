<?php

namespace Packages\PdfRenderer\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Listeners\RenderInvoice;
use Packages\PdfRenderer\Services\PdfRenderer;

class PdfRendererServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(PdfRenderer::class, function ($app) {
            return new PdfRenderer();
        });

        // Register the event listener
        Event::listen(
            InvoiceAssembled::class,
            RenderInvoice::class
        );

        // Register the view namespace
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'pdf-renderer');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
} 