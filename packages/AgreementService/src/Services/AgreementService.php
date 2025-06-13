<?php

namespace Packages\AgreementService\Services;

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
        // For now, return a hardcoded agreement.
        // The customerId is ignored in this placeholder.
        return [
            'version' => 'v1.2',
            'multiplier' => 1.15, // 115%
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => ' Charge',
            ]
        ];
    }
} 