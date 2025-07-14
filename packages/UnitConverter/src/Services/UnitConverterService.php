<?php

namespace InvoicingEngine\UnitConverter\Services;

class UnitConverterService
{
    public function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public function toNanograms(float $weight): int
    {
        return (int) round($weight * 1_000_000_000);
    }

    public function toMillimeters(float $distance): int
    {
        return (int) round($distance * 1000);
    }
}