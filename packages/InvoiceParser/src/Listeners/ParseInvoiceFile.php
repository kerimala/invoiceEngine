<?php

namespace Packages\InvoiceParser\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Services\InvoiceParserService;

class ParseInvoiceFile implements ShouldQueue
{
    public function __construct()
    {
    }

    public function handle(FileStored $event): void
    {
        app(InvoiceParserService::class)->parse($event->filePath);
    }
} 