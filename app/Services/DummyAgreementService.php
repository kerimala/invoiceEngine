<?php
// app/Services/DummyAgreementService.php

namespace App\Services;

/**
 * A default AgreementService that always returns 100 (100% — no discount).
 * We bind this in AppServiceProvider so that if you forget to swap in a real
 * pricing‐lookup service, the app will at least run without errors.
 */
class DummyAgreementService implements AgreementService
{
    public function getMultiplier(string $customerId): int
    {
        // Always “no discount” for now (100%).
        return 100;
    }
}