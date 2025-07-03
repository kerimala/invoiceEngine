<?php

namespace Packages\InvoiceAssembler\DTOs;

class InvoiceLine
{
    private string $description;
    private int $quantity;
    private float $unitPrice;
    private float $total;
    private ?string $productName = null;
    private string $currency;
    private string $agreementVersion;
    private bool $lastLine;

    public function __construct(
        string $description,
        float $quantity,
        float $unitPrice,
        ?string $productName = null,
        string $currency = 'USD',
        string $agreementVersion = '1.0',
        bool $lastLine = false
    ) {
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->productName = $productName;
        $this->currency = $currency;
        $this->agreementVersion = $agreementVersion;
        $this->lastLine = $lastLine;
        $this->total = $quantity * $unitPrice;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->updateTotal();
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
        $this->updateTotal();
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function getProductName(): ?string
    {
        return $this->productName ?? $this->description;
    }

    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    private function updateTotal(): void
    {
        $this->total = $this->quantity * $this->unitPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getAgreementVersion(): string
    {
        return $this->agreementVersion;
    }

    public function setAgreementVersion(string $agreementVersion): void
    {
        $this->agreementVersion = $agreementVersion;
    }

    public function isLastLine(): bool
    {
        return $this->lastLine;
    }

    public function setLastLine(bool $lastLine): void
    {
        $this->lastLine = $lastLine;
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'product_name' => $this->getProductName(),
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->total,
            'currency' => $this->currency,
            'agreement_version' => $this->agreementVersion,
            'last_line' => $this->lastLine,
        ];
    }
}