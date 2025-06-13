<?php

namespace Packages\InvoiceAssembler\Services;

use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceAssembler\DTOs\InvoiceLine;

class InvoiceAssembler
{
    public function createInvoice(array $lines): Invoice
    {
        $invoiceLines = array_map(
            fn(array $line) => new InvoiceLine(
                $line['product_name'],
                $line['quantity'],
                $line['unit_price'],
                $line['total']
            ),
            $lines
        );

        $invoice = new Invoice('INV-'.uniqid(), 'Some Customer');
        $invoice->setLines($invoiceLines);

        return $invoice;
    }
} 