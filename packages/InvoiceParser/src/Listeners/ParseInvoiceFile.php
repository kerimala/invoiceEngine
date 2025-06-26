<?php

namespace Packages\InvoiceParser\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Services\InvoiceParserService;
use Illuminate\Support\Facades\Log;

class ParseInvoiceFile implements ShouldQueue
{
    public function __construct()
    {
    }

    public function handle(FileStored $event): void
    {
        Log::info('ParseInvoiceFile listener handled for file: ' . $event->filePath);
        app(InvoiceParserService::class)->parse($event->filePath);
    }
} 