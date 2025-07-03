<?php

namespace Packages\AgreementService\Services;

use Illuminate\Support\Facades\Log;

class AgreementService
{
    /**
     * Fetches the agreement for a given customer.
     * In a real implementation, this would involve a database query
     * or a call to an external service.
     *
     * @param string $customerId
     * @return array
     */
    public function getAgreementForCustomer(string $customerId): array
    {
        Log::info('Fetching agreement for customer.', ['customerId' => $customerId]);
        // For now, return a hardcoded agreement.
        // The customerId is ignored in this placeholder.
        $agreement = [
            'version' => 'v1.2',
            'multiplier' => 1.15,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => 'Charge',
            ]
        ];

        Log::info('Agreement found for customer.', ['customerId' => $customerId, 'agreement_version' => $agreement['version']]);
        return $agreement;
    }
}