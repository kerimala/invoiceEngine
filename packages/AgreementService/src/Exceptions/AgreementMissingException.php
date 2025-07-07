<?php

namespace Packages\AgreementService\Exceptions;

use Exception;

class AgreementMissingException extends Exception
{
    private string $customerId;
    private ?string $invoiceId;
    
    public function __construct(string $customerId, string $invoiceId = null, string $message = null)
    {
        $this->customerId = $customerId;
        $this->invoiceId = $invoiceId;
        
        $defaultMessage = "Agreement Missing: No valid agreement found for customer '{$customerId}'";
        if ($invoiceId) {
            $defaultMessage .= " (Invoice ID: {$invoiceId})";
        }
        
        parent::__construct($message ?? $defaultMessage, 422);
    }
    
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
    
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }
}