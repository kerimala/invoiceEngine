<?php

namespace Packages\InvoiceAssembler\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;

class InvoiceAssemblerService
{
    /**
     * Add a priced line to a temporary invoice cache.
     * When the last line for a file is received, assemble the full invoice
     * and dispatch the InvoiceAssembled event.
     *
     * @param array $pricedLine
     * @param string $originalFilePath
     */
    public function assemble(array $pricedLine, string $originalFilePath): void
    {
        $cacheKey = 'invoice_lines_' . md5($originalFilePath);
        Log::info('Assembling invoice line.', ['filePath' => $originalFilePath, 'cacheKey' => $cacheKey]);

        // Append the new line to the cached lines for this file
        $lines = Cache::get($cacheKey, []);
        $lines[] = $pricedLine;
        Cache::put($cacheKey, $lines, now()->addHour()); // Cache for 1 hour

        Log::debug('Line added to cache.', ['cacheKey' => $cacheKey, 'line_count' => count($lines)]);

        // If this is the last line, assemble the invoice
        if (isset($pricedLine['last_line']) && $pricedLine['last_line'] === true) {
            Log::info('Last line received. Assembling final invoice.', ['cacheKey' => $cacheKey, 'total_lines' => count($lines)]);
            
            // In a real scenario, we might create a more complex Invoice object.
            // For now, we'll just use the array of lines.
            $finalInvoiceData = [
                'invoice_id' => 'INV-' . uniqid(),
                'customer_id' => 'some_customer_id', // This should probably come from earlier steps
                'file_path' => $originalFilePath,
                'lines' => $lines,
                'total_amount' => array_sum(array_column($lines, 'line_total')),
                'currency' => $lines[0]['currency'] ?? 'EUR',
            ];

            Log::info('Final invoice data prepared.', ['invoice_id' => $finalInvoiceData['invoice_id']]);

            // Dispatch the event with the fully assembled invoice
            Event::dispatch(new InvoiceAssembled($finalInvoiceData));
            Log::info('Dispatched InvoiceAssembled event.', ['invoice_id' => $finalInvoiceData['invoice_id']]);

            // Clean up the cache
            Cache::forget($cacheKey);
            Log::info('Cache cleared for file.', ['cacheKey' => $cacheKey]);
        }
    }
} 