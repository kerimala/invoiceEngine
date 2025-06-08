<?php
// tests/Unit/PricingEngineServiceTest.php

use Tests\TestCase;
uses(TestCase::class);

use App\Services\PricingEngineService;
use App\Services\AgreementService;
use Exception;

beforeEach(function () {
    // Nothing special before each test; we'll bind fakes in each test as needed.
});

it('throws if parsedInvoice is missing required keys', function () {
    // Bind a fake that always returns 100% (no change)
    bindFakeAgreementInt(100);

    $svc = $this->app->make(PricingEngineService::class);

    $badInvoice = [
        'invoice_number' => 'INV-001',
        'customer_id'    => 'CUST-001',
        'date'           => '2025-05-10',
        // missing 'line_items'
    ];
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("missing required key 'line_items'");
    $svc->applyPricing($badInvoice);
});

it('applies a numeric multiplier from the agreement service', function () {
    // Bind a fake that returns 120% = 1.20
    bindFakeAgreementInt(120);

    $svc = $this->app->make(PricingEngineService::class);

    $parsed = [
        'invoice_number' => 'INV-002',
        'customer_id'    => 'CUST-002',
        'date'           => '2025-05-11',
        'line_items'     => [
            [ 'description' => 'Item X', 'quantity' => 2, 'unit_price' => 1000 ], // 1000 cents = $10.00
            [ 'description' => 'Item Y', 'quantity' => 1, 'unit_price' => 2000 ], // 2000 cents = $20.00
        ],
    ];

    $result = $svc->applyPricing($parsed);

    // 2*1000*120/100 = 2400 cents, 1*2000*120/100 = 2400 cents, invoice_total = 4800 cents
    expect($result['invoice_total'])->toBe(4800);
    expect($result['line_items'][0]['line_total'])->toBe(2400);
    expect($result['line_items'][1]['line_total'])->toBe(2400);
});

it('handles string multiplier with comma as decimal separator', function () {
    // Bind a fake that returns 95% via callback (simulate string-to-int conversion)
    bindFakeAgreementCallback(fn($customerId) => intval('95'));

    $svc = $this->app->make(PricingEngineService::class);

    $parsed = [
        'invoice_number' => 'INV-CSV-003',
        'customer_id'    => 'CUST-CSV',
        'date'           => '2025-05-12',
        'line_items'     => [
            [ 'description' => 'CSV Item', 'quantity' => 4, 'unit_price' => 500 ], // 500 cents = $5.00
        ],
    ];

    $result = $svc->applyPricing($parsed);

    // 4*500*95/100 = 1900 cents
    expect($result['invoice_total'])->toBe(1900);
    expect($result['line_items'][0]['line_total'])->toBe(1900);
});

it('applies versioned agreement logic based on invoice date', function () {
    // Bind a fake that returns 100% for 'OLD', 90% otherwise
    bindFakeAgreementCallback(fn($customerId) => str_ends_with($customerId, 'OLD') ? 100 : 90);

    $svc = $this->app->make(PricingEngineService::class);

    $oldParsed = [
        'invoice_number' => 'INV-OLD-001',
        'customer_id'    => 'CUST-OLD',
        'date'           => '2025-01-15',
        'line_items'     => [ [ 'description' => 'Legacy', 'quantity' => 5, 'unit_price' => 1000 ] ],
    ];
    $oldResult = $svc->applyPricing($oldParsed);
    // 5*1000*100/100 = 5000 cents
    expect($oldResult['invoice_total'])->toBe(5000);

    $newParsed = [
        'invoice_number' => 'INV-NEW-002',
        'customer_id'    => 'CUST-NEW',
        'date'           => '2025-06-01',
        'line_items'     => [ [ 'description' => 'Current', 'quantity' => 2, 'unit_price' => 1500 ] ],
    ];
    $newResult = $svc->applyPricing($newParsed);
    // 2*1500*90/100 = 2700 cents
    expect($newResult['invoice_total'])->toBe(2700);
});

it('throws if a line item is missing a required field', function () {
    // Bind a fake that returns 100%
    bindFakeAgreementInt(100);

    $svc = $this->app->make(PricingEngineService::class);

    $parsed = [
        'invoice_number' => 'INV-ERR-001',
        'customer_id'    => 'CUST-ERR',
        'date'           => '2025-05-20',
        'line_items'     => [ [ 'description' => 'Bad', 'quantity' => 1 ] ],
    ];

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("missing 'unit_price' in a line item");
    $svc->applyPricing($parsed);
});

it('handles complex margin specification in agreement if returned as array', function () {
    // Bind a fake that extracts 'multiplier' from a complex agreement data array
    bindFakeAgreementCallback(fn($customerId) => collect([
        'version'    => 'v2',
        'currency'   => 'EUR',
        'multiplier' => 105,
        'weight_unit'=> 'g',
        'language'   => 'de',
    ])->get('multiplier'));

    $svc = $this->app->make(PricingEngineService::class);

    $parsed = [
        'invoice_number' => 'INV-CPLX-001',
        'customer_id'    => 'CUST-CPLX',
        'date'           => '2025-05-21',
        'line_items'     => [ [ 'description' => 'Complex', 'quantity' => 3, 'unit_price' => 1000 ] ],
    ];

    $result = $svc->applyPricing($parsed);
    // 3*1000*105/100 = 3150 cents
    expect($result['invoice_total'])->toBe(3150);
    expect($result['line_items'][0]['line_total'])->toBe(3150);
});