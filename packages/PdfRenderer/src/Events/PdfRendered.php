<?php

namespace Packages\PdfRenderer\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class PdfRendered extends ShouldBeStored
{
    public string $pdfPath;
    public array $invoiceData;

    public function __construct(string $pdfPath, array $invoiceData)
    {
        $this->pdfPath = $pdfPath;
        $this->invoiceData = $invoiceData;
    }
}