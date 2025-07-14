<?php

use InvoicingEngine\UnitConverter\Services\UnitConverterService;

test('converts float to cents', function () {
    $service = new UnitConverterService();
    expect($service->toCents(123.45))->toBe(12345);
    expect($service->toCents(0.99))->toBe(99);
    expect($service->toCents(10.00))->toBe(1000);
    expect($service->toCents(0.0))->toBe(0);
});

test('converts grams to nanograms', function () {
    $service = new UnitConverterService();
    expect($service->toNanograms(1))->toBe(1000000000);
    expect($service->toNanograms(0.000000001))->toBe(1);
    expect($service->toNanograms(12.345))->toBe(12345000000);
});

test('converts meters to millimeters', function () {
    $service = new UnitConverterService();
    expect($service->toMillimeters(1))->toBe(1000);
    expect($service->toMillimeters(0.5))->toBe(500);
    expect($service->toMillimeters(12.345))->toBe(12345);
});