<?php

namespace Packages\AgreementService\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\AgreementService\Services\AgreementService;

class AgreementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AgreementService::class, function ($app) {
            return new AgreementService();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}