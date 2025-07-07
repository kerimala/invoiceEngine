<?php

namespace Packages\InvoiceService\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceService\Services\InvoiceService;

class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService($app->make(\Packages\AgreementService\Services\AgreementService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}