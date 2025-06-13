<?php

namespace Packages\PricingEngine\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Packages\PricingEngine\Listeners\ApplyPricing;
use Packages\PricingEngine\Services\PricingEngineService;

class PricingEngineServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PricingEngineService::class, function ($app) {
            return new PricingEngineService();
        });
    }
} 