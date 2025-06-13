<?php

namespace Packages\InvoiceAssembler\DTOs;

class Invoice
{
    protected string $id;
    protected \DateTime $date;
    protected array $lines = [];
    protected float $totalAmount = 0.0;
    protected string $customerEmail = '';

    public function __construct(string $id = '', string $customerEmail = '')
    {
        $this->id = $id;
        $this->customerEmail = $customerEmail;
        $this->date = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function setLines(array $lines): self
    {
        $this->lines = $lines;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }
}

