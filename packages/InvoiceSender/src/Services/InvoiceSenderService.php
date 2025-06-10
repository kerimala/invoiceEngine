<?php

namespace Packages\InvoiceSender\Services;

use Illuminate\Support\Facades\Log;

class InvoiceSenderService
{
    public function send(string $pdfPath): void
    {
        Log::info("Sending invoice: {$pdfPath}");
    }
} 