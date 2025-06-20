<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AgreementService;
use App\Services\DummyAgreementService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AgreementService::class, DummyAgreementService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
