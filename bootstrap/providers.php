<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    Packages\InvoiceService\Providers\InvoiceServiceProvider::class,
    InvoicingEngine\PricingEngine\Providers\PricingEngineServiceProvider::class,
    Packages\AgreementService\Providers\AgreementServiceProvider::class,
    Packages\InvoiceAssembler\Providers\InvoiceAssemblerServiceProvider::class,
    Packages\InvoiceFileIngest\Providers\InvoiceFileIngestServiceProvider::class,
    Packages\InvoiceParser\Providers\InvoiceParserServiceProvider::class,
    Packages\InvoiceSender\Providers\InvoiceSenderServiceProvider::class,
    Packages\PdfRenderer\Providers\PdfRendererServiceProvider::class,
];
