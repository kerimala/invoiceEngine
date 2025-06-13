<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Listeners\ParseInvoiceFile;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Packages\PricingEngine\Listeners\ApplyPricing;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Packages\InvoiceAssembler\Listeners\AssembleInvoice;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Listeners\RenderPdf;
use Packages\PdfRenderer\Events\PdfRendered;
use Packages\InvoiceSender\Listeners\SendInvoice;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string|string>>
     */
    protected $listen = [
        // Add your event listeners here
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(
            FileStored::class,
            ParseInvoiceFile::class
        );

        Event::listen(
            CarrierInvoiceLineExtracted::class,
            ApplyPricing::class
        );

        Event::listen(
            PricedInvoiceLine::class,
            AssembleInvoice::class
        );

        Event::listen(
            InvoiceAssembled::class,
            RenderPdf::class
        );

        Event::listen(
            PdfRendered::class,
            SendInvoice::class
        );
    }
} 