<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Listeners\ParseInvoiceFile;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use InvoicingEngine\PricingEngine\Listeners\ApplyPricing;
use InvoicingEngine\PricingEngine\Events\PricedInvoiceLine;
use Packages\InvoiceAssembler\Listeners\AssembleInvoice;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;
use Packages\PdfRenderer\Listeners\RenderInvoice;
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
        FileStored::class => [
            ParseInvoiceFile::class,
        ],
        CarrierInvoiceLineExtracted::class => [
            ApplyPricing::class,
        ],
        PricedInvoiceLine::class => [
            AssembleInvoice::class,
        ],
        InvoiceAssembled::class => [
            RenderInvoice::class,
        ],
        PdfRendered::class => [
            SendInvoice::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}