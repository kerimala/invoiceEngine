<?php

namespace InvoicingEngine\PricingEngine\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvoicingEngine\PricingEngine\Services\PricingEngineService;
use InvalidArgumentException;

class PricingEngineServiceTest extends TestCase
{
    use RefreshDatabase;
    private PricingEngineService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(PricingEngineService::class);
    }

    private function getSampleParsedLine(): array
    {
        return [
            'Billing Account' => '6149199',
            'Shipment Number' => '69439739',
            'Weight Charge' => '8.36',
            'XC1 Name' => 'Brandstof',
            'XC1 Charge' => '1.27',
            'XC2 Name' => 'GoGreen',
            'XC2 Charge' => '0.03',
            'XC3 Name' => 'Tol Belgie',
            'XC3 Charge' => '0.37',
            'XC4 Name' => 'Niet-leverb',
            'XC4 Charge' => '17.03',
            'XC5 Name' => '',
            'XC5 Charge' => '',
            'XC6 Name' => '',
            'XC6 Charge' => '',
        ];
    }

    private function getSampleAgreement(): array
    {
        return [
            'version' => 'v1.2',
            'strategy' => 'standard',
            'multiplier' => 1.15,
            'currency' => 'EUR',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => ' Charge',
            ]
        ];
    }

    public function test_it_correctly_calculates_the_line_total_from_spreadsheet_data()
    {
        $parsedLine = $this->getSampleParsedLine();
        $agreement = $this->getSampleAgreement();
        $agreement['vat_rate'] = 0.21;

        $pricedLine = $this->service->priceLine($parsedLine, $agreement);

        // (8.36 [Weight] + 1.27 [XC1] + 0.03 [XC2] + 0.37 [XC3] + 17.03 [XC4]) * 1.15 [multiplier]
        $expectedNettTotal = (8.36 + 1.27 + 0.03 + 0.37 + 17.03) * 1.15;
        $expectedVatAmount = $expectedNettTotal * 0.21;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;

        $this->assertEquals(round($expectedNettTotal, 2), $pricedLine['nett_total']);
        $this->assertEquals(round($expectedVatAmount, 2), $pricedLine['vat_amount']);
        $this->assertEquals(round($expectedTotal, 2), $pricedLine['line_total']);
        $this->assertEquals('v1.2', $pricedLine['agreement_version']);
        $this->assertEquals('EUR', $pricedLine['currency']);
    }

    public function test_it_handles_lines_with_no_surcharges()
    {
        $parsedLine = $this->getSampleParsedLine();
        // Remove surcharge columns
        for ($i = 1; $i <= 6; $i++) {
            unset($parsedLine["XC{$i} Name"]);
            unset($parsedLine["XC{$i} Charge"]);
        }
        
        $agreement = $this->getSampleAgreement();
        $agreement['vat_rate'] = 0.21;

        $pricedLine = $this->service->priceLine($parsedLine, $agreement);
        
        $expectedNettTotal = 8.36 * 1.15;
        $expectedVatAmount = $expectedNettTotal * 0.21;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;

        $this->assertEquals(round($expectedNettTotal, 2), $pricedLine['nett_total']);
        $this->assertEquals(round($expectedVatAmount, 2), $pricedLine['vat_amount']);
        $this->assertEquals(round($expectedTotal, 2), $pricedLine['line_total']);
    }

    public function test_it_throws_an_exception_if_agreement_is_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->priceLine([], ['version' => 'v1', 'rules' => []]);
    }
}