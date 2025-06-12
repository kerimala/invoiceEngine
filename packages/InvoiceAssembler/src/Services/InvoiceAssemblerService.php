<?php

namespace Packages\InvoiceAssembler\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;

class InvoiceAssemblerService
{
    public function assemble(array $pricedLine, string $filePath): void
    {
        $invoiceLines = Cache::get($filePath, []);
        $invoiceLines[] = $pricedLine;
        Cache::put($filePath, $invoiceLines);

        if (isset($pricedLine['last_line']) && $pricedLine['last_line'] === true) {
            $invoiceData = [
                'filePath' => $filePath,
                'lines' => $invoiceLines,
            ];
            Event::dispatch(new InvoiceAssembled($invoiceData));
            Cache::forget($filePath);
        }
    }
} 