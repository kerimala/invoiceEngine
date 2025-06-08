<?php
namespace App\Services;

interface AgreementService
{
    /** 
     * Return an integer multiplier (e.g. 120 means 120%).
     */
    public function getMultiplier(string $customerId): int;
}