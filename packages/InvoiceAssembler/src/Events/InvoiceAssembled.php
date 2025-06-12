<?php

namespace Packages\InvoiceAssembler\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class InvoiceAssembled extends ShouldBeStored
{
    public array $invoiceData;

    public function __construct(array $invoiceData)
    {
        $this->invoiceData = $invoiceData;
    }
} 