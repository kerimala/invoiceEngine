<?php

namespace Packages\InvoiceSender\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class InvoiceSent extends ShouldBeStored
{
    public string $pdfPath;

    public function __construct(string $pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }
} 