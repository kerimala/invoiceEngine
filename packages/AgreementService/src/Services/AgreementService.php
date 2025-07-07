<?php

namespace Packages\AgreementService\Services;

use App\Models\Agreement;

class AgreementService
{
    public function getAgreementForCustomer(string $customerId): ?array
    {
        $agreement = Agreement::where('customer_id', $customerId)
            ->orderBy('version', 'desc')
            ->first();

        if (!$agreement) {
            return null;
        }

        return $agreement->toArray();
    }

    public function createNewVersion(string $billingAccount, array $data): Agreement
    {
        $latestAgreement = Agreement::where('customer_id', $billingAccount)
            ->orderBy('version', 'desc')
            ->first();

        $newVersion = $latestAgreement ? $latestAgreement->version + 1 : 1;

        return Agreement::create(array_merge($data, [
            'customer_id' => $billingAccount,
            'version' => $newVersion,
            'valid_from' => now(),
        ]));
    }

    public function calculateAmount(Agreement $agreement): float
    {
        // This is a simplified calculation. A more complex implementation would
        // involve a strategy pattern to handle different calculation rules.
        return 100 * $agreement->multiplier;
    }
}