<?php

namespace Packages\InvoiceAssembler\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Packages\InvoiceAssembler\Services\InvoiceAssemblerService;
use Packages\PricingEngine\Events\PricedInvoiceLine;

class AssembleInvoice implements ShouldQueue
{
    public function __construct(private readonly InvoiceAssemblerService $invoiceAssemblerService)
    {
    }

    public function handle(PricedInvoiceLine $event): void
    {
        Log::info('AssembleInvoice listener received a PricedInvoiceLine event.', ['filePath' => $event->filePath]);
        $this->invoiceAssemblerService->assemble($event->pricedLine, $event->filePath);
    }
} 