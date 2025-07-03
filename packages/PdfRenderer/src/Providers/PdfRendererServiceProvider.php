<?php

namespace Packages\PdfRenderer\Providers;

use Illuminate\Support\ServiceProvider;
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

        // Event listener is registered in EventServiceProvider
        // Removed duplicate registration

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