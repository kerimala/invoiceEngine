<?php

namespace Packages\InvoiceAssembler\DTOs;

class Invoice
{
    private string $invoiceId;
    private string $customerEmail;
    private array $lines = [];
    private float $totalAmount = 0.0;
    private string $currency = 'EUR';
    private ?string $customerId = null;
    private ?string $filePath = null;

    public function __construct(string $invoiceId, string $customerEmail)
    {
        $this->invoiceId = $invoiceId;
        $this->customerEmail = $customerEmail;
    }

    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setLines(array $lines): void
    {
        $this->lines = $lines;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(InvoiceLine $line): void
    {
        $this->lines[] = $line;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }
}