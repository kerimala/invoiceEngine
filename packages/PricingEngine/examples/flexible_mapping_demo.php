<?php

/**
 * Demonstration script showing how flexible column mapping works
 * with different table formats in the PricingEngine.
 * 
 * This script can be run independently to see how the same pricing logic
 * can be applied to completely different table structures.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use InvoicingEngine\PricingEngine\Interfaces\PricingStrategyInterface;

/**
 * Flexible Standard Pricing Strategy that demonstrates configurable column mapping
 */
class FlexibleStandardPricingStrategy implements PricingStrategyInterface
{
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
}

// Demo function to show different table formats
function demonstrateFlexibleMapping()
{
    echo "\n=== Flexible Column Mapping Demonstration ===\n\n";
    
    $strategy = new FlexibleStandardPricingStrategy();
    
    // Scenario 1: Traditional DHL-style format
    echo "1. Traditional Carrier Format (DHL-style):\n";
    $dhlData = [
        'Billing Account' => '6149199',
        'Shipment Number' => '69439739', 
        'Weight Charge' => '8.36',
        'XC1 Name' => 'Brandstof',
        'XC1 Charge' => '1.27',
        'XC2 Name' => 'GoGreen',
        'XC2 Charge' => '0.03'
    ];
    
    $dhlAgreement = [
        'version' => 'v1.0',
        'strategy' => 'standard',
        'multiplier' => 1.15,
        'vat_rate' => 0.21,
        'currency' => 'EUR',
        'rules' => [
            'base_charge_column' => 'Weight Charge',
            'surcharge_columns' => ['XC1 Charge', 'XC2 Charge']
        ]
    ];
    
    $result1 = $strategy->calculate($dhlData, $dhlAgreement);
    echo "Input columns: " . implode(', ', array_keys($dhlData)) . "\n";
    echo "Total: €{$result1['line_total']} (Nett: €{$result1['nett_total']}, VAT: €{$result1['vat_amount']})\n\n";
    
    // Scenario 2: US Carrier with different terminology
    echo "2. US Carrier Format (FedEx-style):\n";
    $fedexData = [
        'Account_Number' => 'US123456',
        'Tracking_ID' => 'FX987654321',
        'Base_Rate' => '15.75',
        'Fuel_Surcharge' => '3.20',
        'Residential_Fee' => '4.50',
        'Saturday_Delivery' => '12.00'
    ];
    
    $fedexAgreement = [
        'version' => 'v2.0',
        'strategy' => 'standard',
        'multiplier' => 1.08,
        'vat_rate' => 0.0875, // US sales tax
        'currency' => 'USD',
        'rules' => [
            'base_charge_column' => 'Base_Rate',
            'surcharge_columns' => ['Fuel_Surcharge', 'Residential_Fee', 'Saturday_Delivery']
        ]
    ];
    
    $result2 = $strategy->calculate($fedexData, $fedexAgreement);
    echo "Input columns: " . implode(', ', array_keys($fedexData)) . "\n";
    echo "Total: $" . $result2['line_total'] . " (Nett: $" . $result2['nett_total'] . ", Tax: $" . $result2['vat_amount'] . ")\n\n";
    
    // Scenario 3: European carrier with localized column names
    echo "3. German Carrier Format (Deutsche Post-style):\n";
    $deutschePostData = [
        'Kundennummer' => 'DE789012',
        'Sendungsnummer' => 'DP456789123',
        'Grundpreis' => '12.40',
        'Kraftstoffzuschlag' => '2.15',
        'Sicherheitszuschlag' => '1.80',
        'Inselzuschlag' => '5.25'
    ];
    
    $deutschePostAgreement = [
        'version' => 'v3.0',
        'strategy' => 'standard',
        'multiplier' => 1.22,
        'vat_rate' => 0.19,
        'currency' => 'EUR',
        'rules' => [
            'base_charge_column' => 'Grundpreis',
            'surcharge_columns' => ['Kraftstoffzuschlag', 'Sicherheitszuschlag', 'Inselzuschlag']
        ]
    ];
    
    $result3 = $strategy->calculate($deutschePostData, $deutschePostAgreement);
    echo "Input columns: " . implode(', ', array_keys($deutschePostData)) . "\n";
    echo "Total: €{$result3['line_total']} (Nett: €{$result3['nett_total']}, MwSt: €{$result3['vat_amount']})\n\n";
    
    // Scenario 4: Minimal format with just essential data
    echo "4. Minimal Carrier Format (Budget carrier):\n";
    $budgetData = [
        'ID' => 'MIN001',
        'Service' => 'Standard',
        'Cost' => '6.99',
        'Extra' => '1.50'
    ];
    
    $budgetAgreement = [
        'version' => 'v4.0',
        'strategy' => 'standard',
        'multiplier' => 1.35,
        'vat_rate' => 0.20,
        'currency' => 'GBP',
        'rules' => [
            'base_charge_column' => 'Cost',
            'surcharge_columns' => ['Extra']
        ]
    ];
    
    $result4 = $strategy->calculate($budgetData, $budgetAgreement);
    echo "Input columns: " . implode(', ', array_keys($budgetData)) . "\n";
    echo "Total: £{$result4['line_total']} (Nett: £{$result4['nett_total']}, VAT: £{$result4['vat_amount']})\n\n";
    
    echo "=== Summary ===\n";
    echo "✓ Processed 4 different table formats\n";
    echo "✓ Each with different column names and structures\n";
    echo "✓ Same pricing logic applied to all formats\n";
    echo "✓ Original data preserved in all results\n";
    echo "✓ Different currencies and tax rates supported\n\n";
    
    echo "This demonstrates that the PricingEngine can handle ANY table format\n";
    echo "as long as the column mappings are properly configured in the agreement rules.\n";
}

// Run the demonstration
if (php_sapi_name() === 'cli') {
    demonstrateFlexibleMapping();
} else {
    echo "<pre>";
    demonstrateFlexibleMapping();
    echo "</pre>";
}