<?php

namespace InvoicingEngine\PricingEngine\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class PricedInvoiceLine extends ShouldBeStored
{
    public array $pricedLine;
    public string $agreement_version;
    public int $line_total;
    public string $filePath;

    public function __construct(array $pricedLine, string $filePath)
    {
        $this->pricedLine = $pricedLine;
        $this->agreement_version = $pricedLine['agreement_version'] ?? '';
        $this->line_total = $pricedLine['line_total'] ?? 0;
        $this->filePath = $filePath;
    }
}