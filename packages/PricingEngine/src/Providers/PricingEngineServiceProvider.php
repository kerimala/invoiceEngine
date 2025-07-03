<?php

namespace InvoicingEngine\PricingEngine\Providers;

use Illuminate\Support\ServiceProvider;
use InvoicingEngine\PricingEngine\Services\PricingEngineService;

class PricingEngineServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PricingEngineService::class, function ($app) {
            return new PricingEngineService();
        });

        // Event listener is registered in EventServiceProvider
        // Removed duplicate registration
    }
}