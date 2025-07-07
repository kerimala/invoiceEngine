# UnitConverter Package

A microservice package for handling unit conversions and locale-based formatting within the Invoicing Engine.

## Features

### Unit Conversion
The `UnitConverterService` provides basic unit conversion functionality:
- **Currency**: Convert amounts to cents (integer representation)
- **Weight**: Convert weight to nanograms (integer representation)
- **Distance**: Convert distance to millimeters (integer representation)

### Locale-Based Formatting
The `FormattingService` provides locale-aware formatting based on Agreement settings:
- **Pricing**: Format currency amounts according to locale and currency from Agreement
- **Weight**: Format weight values with appropriate decimal separators and unit labels
- **Distance**: Format distance values with appropriate decimal separators and unit labels

## Usage

### Basic Unit Conversion

```php
use InvoicingEngine\UnitConverter\Services\UnitConverterService;

$converter = new UnitConverterService();

// Convert currency to cents
$cents = $converter->toCents(123.45); // Returns 12345

// Convert weight to nanograms
$nanograms = $converter->toNanograms(1.5); // Returns 1500000000

// Convert distance to millimeters
$millimeters = $converter->toMillimeters(2.5); // Returns 2500
```

### Locale-Based Formatting

```php
use InvoicingEngine\UnitConverter\Services\FormattingService;
use InvoicingEngine\UnitConverter\Services\UnitConverterService;
use App\Models\Agreement;

$unitConverter = new UnitConverterService();
$formatter = new FormattingService($unitConverter);

// Create an agreement with locale settings
$agreement = new Agreement([
    'currency' => 'EUR',
    'language' => 'de'
]);

// Format pricing
$formattedPrice = $formatter->formatPricing(1234.56, $agreement);
// Output: "1.234,56 €" (German locale)

// Format weight
$formattedWeight = $formatter->formatWeight(2.5, $agreement);
// Output: "2,50 g" (German locale)

// Format distance
$formattedDistance = $formatter->formatDistance(10.75, $agreement);
// Output: "10,75 m" (German locale)
```

## Supported Locales

The FormattingService supports the following languages with appropriate formatting:

- **English (en)**: Decimal point (.), thousands comma (,)
- **German (de)**: Decimal comma (,), thousands point (.)
- **French (fr)**: Decimal comma (,), thousands space ( )
- **Spanish (es)**: Decimal comma (,), thousands point (.)
- **Italian (it)**: Decimal comma (,), thousands point (.)
- **Dutch (nl)**: Decimal comma (,), thousands point (.)
- **Portuguese (pt)**: Decimal comma (,), thousands point (.)
- **Polish (pl)**: Decimal comma (,), thousands space ( )
- **Russian (ru)**: Decimal comma (,), thousands space ( ), Cyrillic units
- **Chinese (zh)**: Chinese unit characters
- **Japanese (ja)**: Japanese unit characters

## Supported Currencies

- EUR (€)
- USD ($)
- GBP (£)
- JPY (¥)
- CHF (CHF)
- CAD (C$)
- AUD (A$)

## Requirements

- PHP 8.0+
- ext-intl (for enhanced locale support)

## Testing

Run the tests using Pest:

```bash
# From the main project directory
vendor/bin/pest packages/UnitConverter/tests/

# Run specific test files
vendor/bin/pest packages/UnitConverter/tests/UnitConverterServiceTest.php
vendor/bin/pest packages/UnitConverter/tests/FormattingServiceTest.php
```

## Integration with Agreement Service

The FormattingService is designed to work seamlessly with the Agreement model, which contains:
- `currency`: The currency code (e.g., 'EUR', 'USD')
- `language`: The language code (e.g., 'en', 'de', 'fr')

This allows for automatic locale-based formatting based on customer agreements.