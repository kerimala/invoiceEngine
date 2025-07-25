<?php

use InvoicingEngine\UnitConverter\Services\FormattingService;
use InvoicingEngine\UnitConverter\Services\UnitConverterService;
use App\Models\Agreement;
use Tests\TestCase;

uses(TestCase::class);

test('formats pricing to locale - EUR with German locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'EUR',
        'locale' => 'de'
    ]);
    
    $result = $service->formatPricing(123.45, $agreement);
    
    // German locale uses comma as decimal separator
    expect($result)->toContain('123,45');
    expect($result)->toContain('€');
});

test('formats pricing to locale - USD with English locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'USD',
        'locale' => 'en'
    ]);
    
    $result = $service->formatPricing(1234.56, $agreement);
    
    // English locale uses dot as decimal separator and comma as thousands separator
    expect($result)->toContain('1,234.56');
    expect($result)->toContain('$');
});

test('formats pricing to locale - GBP with English locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'GBP',
        'locale' => 'en'
    ]);
    
    $result = $service->formatPricing(99.99, $agreement);
    
    expect($result)->toContain('99.99');
    expect($result)->toContain('£');
});

test('formats weight to locale - German', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'de'
    ]);
    
    $result = $service->formatWeight(1.5, $agreement);
    
    expect($result)->toContain('1,50');
    expect($result)->toContain('g');
});

test('formats weight to locale - English', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'en'
    ]);
    
    $result = $service->formatWeight(2.75, $agreement);
    
    expect($result)->toContain('2.75');
    expect($result)->toContain('g');
});

test('formats weight to locale - Russian', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'ru'
    ]);
    
    $result = $service->formatWeight(3.25, $agreement);
    
    expect($result)->toContain('3,25');
    expect($result)->toContain('г'); // Cyrillic 'g'
});

test('formats distance to locale - German', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'de'
    ]);
    
    $result = $service->formatDistance(10.5, $agreement);
    
    expect($result)->toContain('10,50');
    expect($result)->toContain('m');
});

test('formats distance to locale - English', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'en'
    ]);
    
    $result = $service->formatDistance(5.25, $agreement);
    
    expect($result)->toContain('5.25');
    expect($result)->toContain('m');
});

test('formats distance to locale - Chinese', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'zh'
    ]);
    
    $result = $service->formatDistance(7.8, $agreement);
    
    expect($result)->toContain('7.80');
    expect($result)->toContain('米'); // Chinese character for meter
});

test('formats distance to locale - Japanese', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'ja'
    ]);
    
    $result = $service->formatDistance(12.34, $agreement);
    
    expect($result)->toContain('12.34');
    expect($result)->toContain('メートル'); // Japanese for meter
});

test('handles unknown currency with fallback', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'XYZ',
        'locale' => 'en'
    ]);
    
    $result = $service->formatPricing(100.00, $agreement);
    
    expect($result)->toContain('100.00');
    expect($result)->toContain('XYZ');
});

test('handles unknown locale with fallback to English', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'EUR',
        'locale' => 'unknown'
    ]);
    
    $result = $service->formatPricing(50.50, $agreement);
    
    // Should fallback to English formatting
    expect($result)->toContain('50.50');
    expect($result)->toContain('€');
});

test('formats French locale correctly', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'currency' => 'EUR',
        'locale' => 'fr'
    ]);
    
    $result = $service->formatPricing(1234.56, $agreement);
    
    // French uses comma as decimal separator and space as thousands separator
    expect($result)->toContain('1 234,56');
    expect($result)->toContain('€');
});

test('formats date with English locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'en'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('01/15/2024');
});

test('formats date with German locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'de'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('15.01.2024');
});

test('formats date with French locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'fr'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('15/01/2024');
});

test('formats date with Dutch locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'nl'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('15-01-2024');
});

test('formats date with Spanish locale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'es'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('15/01/2024');
});

test('formats date with specific locale using formatDateWithLocale', function () {
    $service = $this->app->make(FormattingService::class);
    
    $date = '2024-01-15';
    $result = $service->formatDateWithLocale($date, 'de');
    
    expect($result)->toBe('15.01.2024');
});

test('formats date with unknown locale falls back to English', function () {
    $service = $this->app->make(FormattingService::class);
    
    $agreement = new Agreement([
        'locale' => 'unknown'
    ]);
    
    $date = '2024-01-15';
    $result = $service->formatDate($date, $agreement);
    
    expect($result)->toBe('01/15/2024');
});