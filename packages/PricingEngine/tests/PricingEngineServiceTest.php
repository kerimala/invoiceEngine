<?php

use Packages\PricingEngine\Services\PricingEngineService;
use Packages\PricingEngine\Events\PricedInvoiceLine;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class);

describe('PricingEngineService', function () {
    it('applies the agreement multiplier to invoice line totals', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => 120];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe(2400);
        expect($result['agreement_version'])->toBe('v1');
    });

    it('throws if agreement version is missing', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['multiplier' => 120];
        expect(fn() => $service->priceLine($parsedLine, $agreement))->toThrow('Agreement version is required');
    });

    it('handles zero multiplier (results in zero total)', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => 0];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe(0);
    });

    it('throws if multiplier is negative', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => -10];
        expect(fn() => $service->priceLine($parsedLine, $agreement))->toThrow('Agreement multiplier must be non-negative');
    });

    it('throws if required invoice line fields are missing', function () {
        $service = new PricingEngineService();
        $agreement = ['version' => 'v1', 'multiplier' => 120];
        $badLine = ['description' => 'Test Item', 'quantity' => 2];
        expect(fn() => $service->priceLine($badLine, $agreement))->toThrow('unit_price is required');
    });

    it('handles decimal multiplier as string', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => '95.5'];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe((int) round(2 * 1000 * 95.5 / 100));
    });

    it('handles multiplier as float (1.2 means 120%)', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => 1.2];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe(2400);
    });

    it('handles missing multiplier (defaults to 100%)', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1'];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe(2000);
    });

    it('handles additional agreement fields (currency, language)', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => 120, 'currency' => 'EUR', 'language' => 'de'];
        $result = $service->priceLine($parsedLine, $agreement);
        expect($result['line_total'])->toBe(2400);
        expect($result['currency'])->toBe('EUR');
        expect($result['language'])->toBe('de');
    });

    it('handles multiple lines (batch pricing)', function () {
        $service = new PricingEngineService();
        $lines = [
            ['description' => 'A', 'quantity' => 1, 'unit_price' => 1000],
            ['description' => 'B', 'quantity' => 2, 'unit_price' => 500],
        ];
        $agreement = ['version' => 'v1', 'multiplier' => 110];
        $results = $service->priceLines($lines, $agreement);
        expect($results[0]['line_total'])->toBe(1100);
        expect($results[1]['line_total'])->toBe(1100);
    });

    it('emits event with correct agreement version', function () {
        Event::fake();

        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = ['version' => 'v1', 'multiplier' => 120];
        $event = $service->priceLineAndEmit($parsedLine, $agreement, '/test/path.csv');
        expect($event)->toBeInstanceOf(PricedInvoiceLine::class);
        expect($event->agreement_version)->toBe('v1');
        expect($event->line_total)->toBe(2400);

        Event::assertDispatched(PricedInvoiceLine::class, function ($event) {
            return $event->line_total === 2400;
        });
        expect($event->filePath)->toBe('/test/path.csv');
    });

    it('handles agreement as an object', function () {
        $service = new PricingEngineService();
        $parsedLine = ['description' => 'Test Item', 'quantity' => 2, 'unit_price' => 1000];
        $agreement = (object) ['version' => 'v1', 'multiplier' => 120];
        $result = $service->priceLine($parsedLine, (array) $agreement);
        expect($result['line_total'])->toBe(2400);
    });

    it('handles agreement version change between lines', function () {
        $service = new PricingEngineService();
        $line1 = ['description' => 'A', 'quantity' => 1, 'unit_price' => 1000];
        $line2 = ['description' => 'B', 'quantity' => 2, 'unit_price' => 500];
        $agreement1 = ['version' => 'v1', 'multiplier' => 100];
        $agreement2 = ['version' => 'v2', 'multiplier' => 200];
        $result1 = $service->priceLine($line1, $agreement1);
        $result2 = $service->priceLine($line2, $agreement2);
        expect($result1['line_total'])->toBe(1000);
        expect($result2['line_total'])->toBe(2000);
        expect($result1['agreement_version'])->toBe('v1');
        expect($result2['agreement_version'])->toBe('v2');
    });
}); 