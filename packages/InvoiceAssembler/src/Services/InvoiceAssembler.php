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
        $customerEmail = 'customer@example.com'; // Default for testing
        
        $invoice = new Invoice($invoiceId, $customerEmail);
        
        $invoiceLines = [];
        $totalAmount = 0;
        
        foreach ($lines as $lineData) {
            $description = $lineData['product_name'] ?? $lineData['description'] ?? 'Unknown Product';
            $quantity = $lineData['quantity'] ?? 1;
            $unitPrice = $lineData['unit_price'] ?? 0;
            
            $invoiceLine = new InvoiceLine($description, $quantity, $unitPrice);
            $invoiceLines[] = $invoiceLine;
            $totalAmount += $invoiceLine->getTotal();
        }
        
        $invoice->setLines($invoiceLines);
        $invoice->setTotalAmount($totalAmount);
        
        return $invoice;
    }
}