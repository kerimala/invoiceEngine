<?php
namespace App\Services;

use Exception;

class PricingEngineService
{
    protected AgreementService $agreementService;

    public function __construct(AgreementService $agreementService)
    {
        $this->agreementService = $agreementService;
    }

    /**
     * Given a parsed invoice array, apply integer‐based multipliers and return a “priced” array.
     */
    public function applyPricing(array $parsedInvoice): array
    {
        throw new Exception('PricingEngineService::applyPricing() not implemented.');
    }
}