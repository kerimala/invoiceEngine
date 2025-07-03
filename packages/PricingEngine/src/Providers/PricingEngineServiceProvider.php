<?php

namespace InvoicingEngine\PricingEngine\Providers;

use Illuminate\Support\ServiceProvider;
use InvoicingEngine\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Listeners\ApplyPricing;
use InvoicingEngine\PricingEngine\Services\PricingEngineService;
use Illuminate\Support\Facades\Event;

class PricingEngineServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PricingEngineService::class, function ($app) {
            return new PricingEngineService();
        });

        // Register the listener
        Event::listen(CarrierInvoiceLineExtracted::class, ApplyPricing::class);
    }
}