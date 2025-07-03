<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    Packages\InvoiceFileIngest\Providers\InvoiceFileIngestServiceProvider::class,
    Packages\InvoiceParser\Providers\InvoiceParserServiceProvider::class,
    Packages\AgreementService\Providers\AgreementServiceProvider::class,
    InvoicingEngine\PricingEngine\Providers\PricingEngineServiceProvider::class,
    Packages\InvoiceAssembler\Providers\InvoiceAssemblerServiceProvider::class,
    Packages\PdfRenderer\Providers\PdfRendererServiceProvider::class,
    Packages\InvoiceSender\Providers\InvoiceSenderServiceProvider::class,
];
