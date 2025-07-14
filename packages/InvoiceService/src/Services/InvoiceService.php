<?php

namespace Packages\InvoiceService\Services;

use App\Models\Agreement;
use Packages\AgreementService\Services\AgreementService;

class InvoiceService
{
    private $agreementService;

    public function __construct(AgreementService $agreementService)
    {
        $this->agreementService = $agreementService;
    }

    public function generateInvoiceForAgreement(Agreement $agreement)
    {
        $calculatedAmount = $this->agreementService->calculateAmount($agreement);
        
        return [
            'agreement_id' => $agreement->id,
            'customer_id' => $agreement->customer_id,
            'amount' => $calculatedAmount,
            'currency' => $agreement->currency,
            'vat_rate' => $agreement->vat_rate,
            'vat_amount' => $calculatedAmount * ($agreement->vat_rate / 100),
            'total_amount' => $calculatedAmount * (1 + $agreement->vat_rate / 100),
            'locale' => $agreement->locale,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}