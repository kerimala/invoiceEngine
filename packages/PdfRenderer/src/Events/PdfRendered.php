<?php

namespace Packages\PdfRenderer\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class PdfRendered extends ShouldBeStored
{
    public string $pdfPath;

    public function __construct(string $pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }
} 