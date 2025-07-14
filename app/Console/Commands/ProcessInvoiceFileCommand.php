<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;

class ProcessInvoiceFileCommand extends Command
{
    protected $signature = 'app:process-invoice-file {filepath}';
    protected $description = 'Process an invoice file';

    public function handle(InvoiceFileIngestService $ingestService)
    {
        $filepath = $this->argument('filepath');

        if (!file_exists($filepath)) {
            $this->error("File not found: {$filepath}");
            return 1;
        }

        try {
            $ingestService->ingest($filepath);
            $this->info("File processing started for: {$filepath}");
        } catch (\Exception $e) {
            $this->error("Failed to process file: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
