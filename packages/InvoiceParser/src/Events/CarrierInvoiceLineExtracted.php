<?php

namespace Packages\InvoiceParser\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CarrierInvoiceLineExtracted extends ShouldBeStored
{
    public string $filePath;
    public int $lineCount;
    public array $parsedLines;

    public function __construct(string $filePath, int $lineCount, array $parsedLines)
    {
        $this->filePath = $filePath;
        $this->lineCount = $lineCount;
        $this->parsedLines = $parsedLines;
    }
} 