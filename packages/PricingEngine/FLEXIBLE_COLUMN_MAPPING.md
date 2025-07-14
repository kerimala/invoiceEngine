# Flexible Column Mapping in PricingEngine

This document explains how the PricingEngine package supports flexible column mapping to work with invoice tables of any format and naming convention.

## Overview

The PricingEngine is designed to handle invoice data from various carriers and sources, each potentially using different column names and formats. Instead of hardcoding column names, the system uses configurable column mappings defined in agreement rules.

## How It Works

### Standard Pricing Strategy

Instead of hardcoding `'Weight Charge'`, the strategy can be configured to use any column name:

```php
// Agreement configuration
'rules' => [
    'base_charge_column' => 'Base_Cost',  // Instead of 'Weight Charge'
    'surcharge_columns' => [
        'Fuel_Surcharge',
        'Security_Fee', 
        'Remote_Area_Fee'
    ]
]
```

### Tiered Pricing Strategy

The quantity column can be mapped to any field:

```php
'rules' => [
    'quantity_column' => 'Package_Count',  // Instead of 'Quantity'
    'tiers' => [
        ['up_to' => 50, 'rate' => 2.50],
        ['up_to' => 100, 'rate' => 2.00]
    ]
]
```

### Volume and Distance Strategy

Both volume and distance columns are configurable:

```php
'rules' => [
    'volume_column' => 'Volumen_m3',      // German-style column name
    'distance_column' => 'Entfernung_km', // German-style column name
    'base_rate' => 15.00,
    'volume_rate' => 8.50,
    'distance_rate' => 0.12
]
```

## Supported Table Formats

### Example 1: US Carrier Format
```
Account_ID | Tracking_Reference | Base_Cost | Fuel_Surcharge | Security_Fee
CUST001   | TRK123456         | 12.50     | 2.30           | 1.75
```

### Example 2: European Carrier Format (German)
```
Kunden_Nummer | Sendung_ID | Volumen_m3 | Entfernung_km | Grundpreis
DE001       | SND456789  | 2.5        | 450           | 8.50
```

### Example 3: Package-based Format
```
Customer_Reference | Service_Type | Package_Count | Destination_Zone
REF789            | Express      | 150           | Zone_A
```

## Benefits

1. **Carrier Independence**: Work with any carrier's data format without code changes
2. **Localization Support**: Handle column names in different languages
3. **Business Logic Flexibility**: Adapt to different business models and pricing structures

## Testing

The `FlexibleColumnMappingTest` class provides comprehensive tests demonstrating:

1. **Alternative Column Names**: Standard pricing with completely different column terminology
2. **Custom Quantity Columns**: Tiered pricing using non-standard quantity field names
3. **Localized Columns**: Volume/distance pricing with German/Dutch column names
4. **Multiple Formats**: Integration test showing multiple table formats in one session

## Implementation Status

✅ **Implemented** - All pricing strategy classes have been updated to support flexible column mapping.

### Completed Features
1. ✅ Updated `StandardPricingStrategy` to use configurable `base_charge_column` and `surcharge_columns`
2. ✅ Updated `TieredPricingStrategy` to use configurable `quantity_column`
3. ✅ Updated `VolumeAndDistanceStrategy` to use configurable `volume_column` and `distance_column`
4. ✅ Comprehensive test coverage for all flexible mapping scenarios
5. ✅ Backward compatibility with existing hardcoded column names

## Implementation Notes

- All strategy classes now support flexible column mapping through agreement rules
- Column mappings are defined in the agreement's `rules` section
- Original column data is always preserved in the output
- Backward compatibility maintained for existing implementations

## Usage Examples

### Standard Pricing with Custom Columns
```php
$agreementRules = [
    'strategy' => 'standard',
    'base_charge_column' => 'Base_Cost',
    'surcharge_columns' => ['Fuel_Surcharge', 'Handling_Fee'],
    'multiplier' => 1.2,
    'vat_rate' => 0.21
];
```

### Tiered Pricing with Localized Quantity Column
```php
$agreementRules = [
    'strategy' => 'tiered',
    'quantity_column' => 'Anzahl', // German for "quantity"
    'tiers' => [
        ['min' => 0, 'max' => 10, 'rate' => 5.0],
        ['min' => 11, 'max' => 50, 'rate' => 4.5]
    ],
    'vat_rate' => 0.19
];
```

### Volume & Distance with Custom Columns
```php
$agreementRules = [
    'strategy' => 'volume_distance',
    'volume_column' => 'Volumen_m3',
    'distance_column' => 'Entfernung_km',
    'base_rate' => 10.0,
    'volume_rate' => 2.5,
    'distance_rate' => 0.8,
    'vat_rate' => 0.19
];
```

## Future Enhancements

- Add column validation and error handling
- Implement auto-detection for common column variations
- Add support for column transformations (e.g., unit conversions)
- Implement column aliases for backward compatibility
- Add support for nested column mappings