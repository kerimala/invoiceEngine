<?php

namespace InvoicingEngine\PricingEngine\Tests;

use Tests\TestCase;
use InvoicingEngine\PricingEngine\Strategies\StandardPricingStrategy;

class StandardPricingStrategyTest extends TestCase
{
    private StandardPricingStrategy $strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->strategy = new StandardPricingStrategy();
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
        ];
    }

    private function getSampleAgreement(): array
    {
        return [
            'version' => 'v1.2',
            'strategy' => 'standard',
            'multiplier' => 1.15,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => ' Charge',
            ]
        ];
    }

    public function test_it_calculates_price_correctly()
    {
        $parsedLine = $this->getSampleParsedLine();
        $agreement = $this->getSampleAgreement();

        $pricedLine = $this->strategy->calculate($parsedLine, $agreement);

        $expectedNettTotal = (8.36 + 1.27 + 0.03) * 1.15;
        $expectedVatAmount = $expectedNettTotal * 0.21;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;

        $this->assertEquals(round($expectedNettTotal, 2), $pricedLine['nett_total']);
        $this->assertEquals(round($expectedVatAmount, 2), $pricedLine['vat_amount']);
        $this->assertEquals(round($expectedTotal, 2), $pricedLine['line_total']);
    }
}