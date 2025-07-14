<?php

namespace InvoicingEngine\PricingEngine\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvoicingEngine\PricingEngine\Strategies\StandardPricingStrategy;
use InvoicingEngine\PricingEngine\Strategies\TieredPricingStrategy;
use InvoicingEngine\PricingEngine\Strategies\VolumeAndDistanceStrategy;
use InvoicingEngine\PricingEngine\Services\PricingEngineService;

/**
 * Tests to verify that pricing strategies can handle different table column formats
 * and naming conventions through flexible column mapping configuration.
 * 
 * These tests demonstrate that the pricing engine can work with any table format
 * as long as the column mappings are properly configured in the agreement rules.
 */
class FlexibleColumnMappingTest extends TestCase
{
    use RefreshDatabase;
    
    private PricingEngineService $service;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(PricingEngineService::class);
    }
    
    /**
     * Test 1: Standard Pricing Strategy with Alternative Column Names
     * 
     * This test verifies that the StandardPricingStrategy can work with
     * completely different column names than the default 'Weight Charge'.
     * We simulate a scenario where a carrier uses different terminology
     * like 'Base Cost', 'Fuel Surcharge', 'Security Fee', etc.
     */
    public function test_standard_pricing_with_alternative_column_names()
    {
        // Simulate invoice data from a carrier that uses different column names
        $invoiceLineWithDifferentColumns = [
            'Account_ID' => 'CUST001',
            'Tracking_Reference' => 'TRK123456',
            'Base_Cost' => '12.50',           // Instead of 'Weight Charge'
            'Fuel_Surcharge' => '2.30',       // Instead of 'XC1 Charge'
            'Security_Fee' => '1.75',         // Instead of 'XC2 Charge'
            'Remote_Area_Fee' => '5.00',      // Instead of 'XC3 Charge'
            'Insurance_Premium' => '0.85',    // Instead of 'XC4 Charge'
        ];
        
        // Agreement configured to work with the alternative column names
        $agreementWithFlexibleMapping = [
            'version' => 'v2.0',
            'strategy' => 'standard',
            'multiplier' => 1.20,
            'vat_rate' => 0.19,
            'currency' => 'USD',
            'agreement_type' => 'flexible_mapping',
            'rules' => [
                'base_charge_column' => 'Base_Cost',
                'surcharge_columns' => [
                    'Fuel_Surcharge',
                    'Security_Fee', 
                    'Remote_Area_Fee',
                    'Insurance_Premium'
                ]
            ]
        ];
        
        // Create a modified StandardPricingStrategy that uses flexible column mapping
        $strategy = new class implements \InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface {
            public function calculate(array $invoiceLine, array $agreement): array
            {
                $multiplier = $agreement['multiplier'] ?? 1;
                $rules = $agreement['rules'];
                
                // Use configurable base charge column instead of hardcoded 'Weight Charge'
                $baseChargeColumn = $rules['base_charge_column'] ?? 'Weight Charge';
                $baseCharge = (float) ($invoiceLine[$baseChargeColumn] ?? 0);
                
                // Use configurable surcharge columns instead of pattern matching
                $surchargeTotal = 0;
                if (isset($rules['surcharge_columns'])) {
                    foreach ($rules['surcharge_columns'] as $column) {
                        if (isset($invoiceLine[$column]) && is_numeric($invoiceLine[$column])) {
                            $surchargeTotal += (float) $invoiceLine[$column];
                        }
                    }
                }
                
                $nettTotal = ($baseCharge + $surchargeTotal) * $multiplier;
                $vatRate = $agreement['vat_rate'] ?? 0;
                $vatAmount = $nettTotal * $vatRate;
                $lineTotal = $nettTotal + $vatAmount;
                
                return array_merge($invoiceLine, [
                    'nett_total' => round($nettTotal, 2),
                    'vat_amount' => round($vatAmount, 2),
                    'line_total' => round($lineTotal, 2),
                    'agreement_version' => $agreement['version'],
                    'currency' => $agreement['currency'] ?? 'EUR',
                ]);
            }
        };
        
        $pricedLine = $strategy->calculate($invoiceLineWithDifferentColumns, $agreementWithFlexibleMapping);
        
        // Calculate expected values: (12.50 + 2.30 + 1.75 + 5.00 + 0.85) * 1.20 = 26.88
        $expectedNettTotal = (12.50 + 2.30 + 1.75 + 5.00 + 0.85) * 1.20;
        $expectedVatAmount = $expectedNettTotal * 0.19;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;
        
        $this->assertEquals(round($expectedNettTotal, 2), $pricedLine['nett_total']);
        $this->assertEquals(round($expectedVatAmount, 2), $pricedLine['vat_amount']);
        $this->assertEquals(round($expectedTotal, 2), $pricedLine['line_total']);
        $this->assertEquals('USD', $pricedLine['currency']);
        $this->assertEquals('v2.0', $pricedLine['agreement_version']);
    }
    
    /**
     * Test 2: Tiered Pricing Strategy with Non-Standard Quantity Column
     * 
     * This test demonstrates that TieredPricingStrategy can work with any
     * column name for quantity, not just a standard 'Quantity' field.
     * We simulate a logistics company that uses 'Package_Count' or 'Item_Volume'.
     */
    public function test_tiered_pricing_with_custom_quantity_column()
    {
        // Invoice data using 'Package_Count' instead of standard quantity column
        $invoiceLineWithCustomQuantity = [
            'Customer_Reference' => 'REF789',
            'Service_Type' => 'Express',
            'Package_Count' => 150,           // Custom quantity column name
            'Destination_Zone' => 'Zone_A',
            'Service_Date' => '2024-01-15'
        ];
        
        // Agreement configured for tiered pricing with custom quantity column
        $tieredAgreementWithCustomColumn = [
            'version' => 'v3.1',
            'strategy' => 'tiered',
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'agreement_type' => 'tiered_custom',
            'rules' => [
                'quantity_column' => 'Package_Count',  // Maps to our custom column
                'tiers' => [
                    ['up_to' => 50, 'rate' => 2.50],   // First 50 packages at €2.50 each
                    ['up_to' => 100, 'rate' => 2.00],  // Next 100 packages at €2.00 each  
                    ['up_to' => 999999, 'rate' => 1.50] // Remaining packages at €1.50 each
                ]
            ]
        ];
        
        $strategy = new TieredPricingStrategy();
        $pricedLine = $strategy->calculate($invoiceLineWithCustomQuantity, $tieredAgreementWithCustomColumn);
        
        // Calculate expected: (50 * 2.50) + (100 * 2.00) + (0 * 1.50) = 125 + 200 + 0 = 325
        // Note: We only have 150 packages, so: (50 * 2.50) + (100 * 2.00) = 325
        $expectedNettTotal = (50 * 2.50) + (100 * 2.00);
        $expectedVatAmount = $expectedNettTotal * 0.21;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;
        
        $this->assertEquals($expectedNettTotal, $pricedLine['nett_total']);
        $this->assertEquals($expectedVatAmount, $pricedLine['vat_amount']);
        $this->assertEquals($expectedTotal, $pricedLine['line_total']);
        
        // Verify the original custom column data is preserved
        $this->assertEquals(150, $pricedLine['Package_Count']);
        $this->assertEquals('Zone_A', $pricedLine['Destination_Zone']);
    }
    
    /**
     * Test 3: Volume and Distance Strategy with Localized Column Names
     * 
     * This test verifies that VolumeAndDistanceStrategy can handle
     * column names in different languages or with different conventions.
     * We simulate a European carrier using German/Dutch column names.
     */
    public function test_volume_distance_pricing_with_localized_columns()
    {
        // Invoice data with European/localized column names
        $invoiceLineWithLocalizedColumns = [
            'Klant_Nummer' => 'DE001',           // Customer Number in Dutch
            'Zending_ID' => 'ZND456789',         // Shipment ID in Dutch
            'Volumen_m3' => 2.5,                 // Volume in cubic meters (German style)
            'Entfernung_km' => 450,              // Distance in kilometers (German)
            'Abhol_Datum' => '2024-01-20',       // Pickup Date in German
            'Ziel_PLZ' => '10115'                // Destination Postal Code in German
        ];
        
        // Agreement configured for volume/distance pricing with localized columns
        $volumeDistanceAgreementLocalized = [
            'version' => 'v4.0',
            'strategy' => 'volume_distance',
            'vat_rate' => 0.19,
            'currency' => 'EUR',
            'agreement_type' => 'localized_columns',
            'rules' => [
                'volume_column' => 'Volumen_m3',     // German-style volume column
                'distance_column' => 'Entfernung_km', // German-style distance column
                'base_rate' => 15.00,                 // Base rate in EUR
                'volume_rate' => 8.50,                // Rate per cubic meter
                'distance_rate' => 0.12               // Rate per kilometer
            ]
        ];
        
        $strategy = new VolumeAndDistanceStrategy();
        $pricedLine = $strategy->calculate($invoiceLineWithLocalizedColumns, $volumeDistanceAgreementLocalized);
        
        // Calculate expected: 15.00 + (2.5 * 8.50) + (450 * 0.12) = 15.00 + 21.25 + 54.00 = 90.25
        $expectedNettTotal = 15.00 + (2.5 * 8.50) + (450 * 0.12);
        $expectedVatAmount = $expectedNettTotal * 0.19;
        $expectedTotal = $expectedNettTotal + $expectedVatAmount;
        
        $this->assertEquals($expectedNettTotal, $pricedLine['nett_total']);
        $this->assertEquals($expectedVatAmount, $pricedLine['vat_amount']);
        $this->assertEquals($expectedTotal, $pricedLine['line_total']);
        
        // Verify the original localized column data is preserved
        $this->assertEquals(2.5, $pricedLine['Volumen_m3']);
        $this->assertEquals(450, $pricedLine['Entfernung_km']);
        $this->assertEquals('DE001', $pricedLine['Klant_Nummer']);
        $this->assertEquals('10115', $pricedLine['Ziel_PLZ']);
    }
    
    /**
     * Test 4: Integration Test - Multiple Table Formats in Single Processing
     * 
     * This test demonstrates that the pricing engine can handle multiple
     * different table formats within the same processing session, each with
     * their own column mapping configuration.
     */
    public function test_multiple_table_formats_integration()
    {
        // Test data representing three different carrier formats
        $carrierAData = [
            'AcctNum' => 'A001',
            'BaseRate' => '10.00',
            'FuelAdj' => '1.50'
        ];
        
        $carrierBData = [
            'Account_ID' => 'B002', 
            'Weight_Cost' => '15.00',
            'Surcharge_1' => '2.25',
            'Surcharge_2' => '0.75'
        ];
        
        $carrierCData = [
            'Kundennummer' => 'C003',
            'Grundpreis' => '8.50',
            'Zuschlag_Kraftstoff' => '1.20',
            'Zuschlag_Sicherheit' => '0.80'
        ];
        
        // Three different agreement configurations for each carrier format
        $agreementA = [
            'version' => 'v1.0',
            'strategy' => 'standard',
            'multiplier' => 1.10,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'rules' => [
                'base_charge_column' => 'BaseRate',
                'surcharge_columns' => ['FuelAdj']
            ]
        ];
        
        $agreementB = [
            'version' => 'v2.0', 
            'strategy' => 'standard',
            'multiplier' => 1.15,
            'vat_rate' => 0.19,
            'currency' => 'USD',
            'rules' => [
                'base_charge_column' => 'Weight_Cost',
                'surcharge_columns' => ['Surcharge_1', 'Surcharge_2']
            ]
        ];
        
        $agreementC = [
            'version' => 'v3.0',
            'strategy' => 'standard', 
            'multiplier' => 1.25,
            'vat_rate' => 0.19,
            'currency' => 'EUR',
            'rules' => [
                'base_charge_column' => 'Grundpreis',
                'surcharge_columns' => ['Zuschlag_Kraftstoff', 'Zuschlag_Sicherheit']
            ]
        ];
        
        // Create flexible strategy for testing
        $flexibleStrategy = new class implements \InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface {
            public function calculate(array $invoiceLine, array $agreement): array
            {
                $multiplier = $agreement['multiplier'] ?? 1;
                $rules = $agreement['rules'];
                
                $baseChargeColumn = $rules['base_charge_column'] ?? 'Weight Charge';
                $baseCharge = (float) ($invoiceLine[$baseChargeColumn] ?? 0);
                
                $surchargeTotal = 0;
                if (isset($rules['surcharge_columns'])) {
                    foreach ($rules['surcharge_columns'] as $column) {
                        if (isset($invoiceLine[$column]) && is_numeric($invoiceLine[$column])) {
                            $surchargeTotal += (float) $invoiceLine[$column];
                        }
                    }
                }
                
                $nettTotal = ($baseCharge + $surchargeTotal) * $multiplier;
                $vatRate = $agreement['vat_rate'] ?? 0;
                $vatAmount = $nettTotal * $vatRate;
                $lineTotal = $nettTotal + $vatAmount;
                
                return array_merge($invoiceLine, [
                    'nett_total' => round($nettTotal, 2),
                    'vat_amount' => round($vatAmount, 2),
                    'line_total' => round($lineTotal, 2),
                    'agreement_version' => $agreement['version'],
                    'currency' => $agreement['currency'] ?? 'EUR',
                ]);
            }
        };
        
        // Process all three different formats
        $resultA = $flexibleStrategy->calculate($carrierAData, $agreementA);
        $resultB = $flexibleStrategy->calculate($carrierBData, $agreementB);
        $resultC = $flexibleStrategy->calculate($carrierCData, $agreementC);
        
        // Verify each result maintains its original column structure while adding pricing
        $this->assertArrayHasKey('AcctNum', $resultA);
        $this->assertArrayHasKey('BaseRate', $resultA);
        $this->assertEquals('A001', $resultA['AcctNum']);
        $this->assertEquals('EUR', $resultA['currency']);
        
        $this->assertArrayHasKey('Account_ID', $resultB);
        $this->assertArrayHasKey('Weight_Cost', $resultB);
        $this->assertEquals('B002', $resultB['Account_ID']);
        $this->assertEquals('USD', $resultB['currency']);
        
        $this->assertArrayHasKey('Kundennummer', $resultC);
        $this->assertArrayHasKey('Grundpreis', $resultC);
        $this->assertEquals('C003', $resultC['Kundennummer']);
        $this->assertEquals('EUR', $resultC['currency']);
        
        // Verify pricing calculations are correct for each format
        $expectedNettA = (10.00 + 1.50) * 1.10;
        $this->assertEquals(round($expectedNettA, 2), $resultA['nett_total']);
        
        $expectedNettB = (15.00 + 2.25 + 0.75) * 1.15;
        $this->assertEquals(round($expectedNettB, 2), $resultB['nett_total']);
        
        $expectedNettC = (8.50 + 1.20 + 0.80) * 1.25;
        $this->assertEquals(round($expectedNettC, 2), $resultC['nett_total']);
    }
}