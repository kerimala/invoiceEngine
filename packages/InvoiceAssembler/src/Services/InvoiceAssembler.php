<?php

namespace Packages\InvoiceAssembler\Services;

use Packages\InvoiceAssembler\DTOs\Invoice;
use Packages\InvoiceAssembler\DTOs\InvoiceLine;

class InvoiceAssembler
{
    /**
     * Create an invoice from an array of line data
     *
     * @param array $lines
     * @return Invoice
     */
    public function createInvoice(array $lines): Invoice
    {
        $invoiceId = 'INV-' . uniqid();
        $customerEmail = $lines[0]['Billing Account'] ?? 'customer@example.com'; // Default for testing
        
        $invoice = new Invoice($invoiceId, $customerEmail);
        
        $invoiceLines = [];
        $totalAmount = 0;

        foreach ($lines as $lineData) {
                        $invoiceLine = new InvoiceLine(
                $lineData['Product Name'] ?? 'Unknown Product',
                1, // quantity
                $lineData['line_total'],
                $lineData['nett_total'],
                $lineData['vat_amount'],
                $lineData['Product Name'] ?? null,
                $lineData['currency'] ?? 'EUR',
                $lineData['agreement_version'] ?? '1.0',
                $lineData['last_line'] ?? false
            );
            $invoiceLines[] = $invoiceLine;
            $totalAmount += $invoiceLine->getTotal();
        }
        
        $invoice->setLines($invoiceLines);
        $invoice->setTotalAmount($totalAmount);
        
        return $invoice;
    }
}