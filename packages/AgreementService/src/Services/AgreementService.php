<?php

namespace Packages\AgreementService\Services;

use App\Models\Agreement;
use Illuminate\Support\Facades\Log;

class AgreementService
{
    public function getAgreementForCustomer(string $customerId): ?array
    {
        $agreement = Agreement::where('customer_id', $customerId)
            ->where('valid_from', '<=', now())
            ->orderBy('valid_from', 'desc')
            ->first();

        $agreementType = 'custom';

        if (!$agreement) {
            Log::info('No custom agreement found, falling back to standard agreement.', ['customerId' => $customerId]);
            $agreement = Agreement::where('customer_id', 'standard')
                ->where('valid_from', '<=', now())
                ->orderBy('valid_from', 'desc')
                ->first();
            $agreementType = 'standard';

            if (!$agreement) {
                Log::error('No standard agreement found.', ['customerId' => $customerId]);
            }
        }

        if (!$agreement) {
            return null;
        }

        Log::info('Applied agreement rule', [
            'agreement_id' => $agreement->id,
            'customer_id' => $customerId,
            'agreement_type' => $agreementType,
            'timestamp' => now()->toIso8601String(),
        ]);

        $result = $agreement->toArray();
        $result['agreement_type'] = $agreementType;

        return $result;
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