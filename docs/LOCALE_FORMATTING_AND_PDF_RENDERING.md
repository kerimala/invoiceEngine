# Locale-Based Formatting and PDF Rendering

This document describes the locale-based formatting system and PDF rendering enhancements implemented in the Invoice Engine.

## Overview

The Invoice Engine now supports locale-aware formatting for invoices, ensuring that numbers, currencies, and other data are displayed according to the customer's regional preferences. This enhancement affects both the PDF generation process and the overall invoice presentation.

## Key Components

### FormattingService

The `FormattingService` is responsible for formatting various types of data based on locale and currency settings:

- **Location**: `packages/UnitConverter/src/Services/FormattingService.php`
- **Purpose**: Provides locale-aware formatting for pricing, weights, distances, and other numerical data
- **Dependencies**: `UnitConverterService` for unit conversions

#### Key Methods

- `formatPricing(float $amount, string $currency, string $language)`: Formats monetary amounts
- `formatWeight(float $weight, string $unit, string $language)`: Formats weight values
- `formatDistance(float $distance, string $unit, string $language)`: Formats distance values

#### Supported Locales

- **German (de)**: Uses comma as decimal separator, period as thousands separator
- **English (en)**: Uses period as decimal separator, comma as thousands separator
- **French (fr)**: Uses comma as decimal separator, space as thousands separator
- **Fallback**: Defaults to English formatting for unsupported locales

### PDF Rendering Integration

The `PdfRenderer` service has been enhanced to support locale-based formatting:

#### Updated PdfRenderer Class

**Location**: `packages/PdfRenderer/src/Services/PdfRenderer.php`

**Key Changes**:
- Injected `FormattingService` and `UnitConverterService` via constructor
- Modified `render` method to accept optional `Agreement` object
- Passes formatting service and agreement data to PDF template

```php
public function render(array $invoice, ?Agreement $agreement = null): string
{
    $formatter = $this->app->make(FormattingService::class);
    
    $pdf = Pdf::view('pdf-renderer::invoice', [
        'invoice' => $invoice,
        'agreement' => $agreement,
        'formatter' => $formatter,
    ]);
    
    // ... rest of the method
}
```

#### Updated PDF Template

**Location**: `packages/PdfRenderer/resources/views/invoice.blade.php`

**Key Changes**:
- Uses `FormattingService` for all monetary and numerical formatting
- Supports fallback to basic `number_format` when formatter is unavailable
- Formats the following fields with locale awareness:
  - `nett_total`
  - `vat_amount` 
  - `line_total`
  - `total_amount`

**Example Template Usage**:
```php
@if(isset($formatter) && isset($agreement))
    {{ $formatter->formatPricing($invoice['nett_total'], $agreement->currency, $agreement->language) }}
@else
    {{ number_format($invoice['nett_total'], 2) }}
@endif
```

## Agreement Integration

The locale-based formatting system integrates with the existing Agreement structure:

### Required Agreement Fields

- `currency`: The currency code (e.g., 'EUR', 'USD', 'GBP')
- `language`: The language/locale code (e.g., 'de', 'en', 'fr')

### Agreement Data Flow

1. **Agreement Retrieval**: The `AgreementService` provides customer-specific agreements
2. **PDF Generation**: The `PdfRenderer` receives the agreement object
3. **Locale Detection**: The formatter uses agreement's language and currency settings
4. **Formatting Application**: All numerical data is formatted according to locale rules

## Testing Coverage

### Unit Tests

**FormattingService Tests** (`packages/UnitConverter/tests/FormattingServiceTest.php`):
- Tests formatting for different locales (German, English, French)
- Verifies currency formatting with various currencies
- Tests fallback behavior for unknown locales/currencies
- Validates weight and distance formatting

### Integration Tests

**PdfRenderer Tests** (`packages/PdfRenderer/tests/PdfRendererTest.php`):
- `test_renders_pdf_with_locale_formatting`: Tests German locale PDF generation
- `test_renders_pdf_with_english_locale_formatting`: Tests English locale PDF generation

**Full Invoice Process Tests** (`tests/Feature/FullInvoiceProcessTest.php`):
- `it_processes_invoice_with_german_locale_and_generates_formatted_pdf`: End-to-end German locale test
- `it_processes_invoice_with_english_locale_and_generates_formatted_pdf`: End-to-end English locale test

## Implementation Details

### Dependency Management

The `PdfRenderer` package has been updated to include the `UnitConverter` dependency:

```json
{
    "require": {
        "vendor/unit-converter": "*"
    }
}
```

### Service Container Integration

All services are properly registered with Laravel's service container, ensuring:
- Proper dependency injection
- Consistent service resolution
- Testability through container mocking

### Backward Compatibility

The implementation maintains backward compatibility:
- PDF generation works without Agreement objects (falls back to basic formatting)
- Existing invoice processing continues to function
- No breaking changes to existing APIs

## Usage Examples

### Generating a Locale-Aware PDF

```php
// Create agreement with German locale
$agreement = Agreement::create([
    'customer_id' => 'customer_123',
    'currency' => 'EUR',
    'language' => 'de',
    'strategy' => 'standard',
    'multiplier' => 1.0,
    'vat_rate' => 19.0,
    'rules' => [],
    'valid_from' => now(),
]);

// Generate PDF with locale formatting
$pdfRenderer = app(PdfRenderer::class);
$pdfPath = $pdfRenderer->render($invoiceData, $agreement);
```

### Manual Formatting

```php
$formatter = app(FormattingService::class);

// Format German pricing
$germanPrice = $formatter->formatPricing(1234.56, 'EUR', 'de');
// Result: "1.234,56 €"

// Format English pricing  
$englishPrice = $formatter->formatPricing(1234.56, 'USD', 'en');
// Result: "$1,234.56"
```

## Future Enhancements

### Planned Features

1. **Additional Locales**: Support for more languages and regional formats
2. **Date Formatting**: Locale-aware date and time formatting
3. **Address Formatting**: Regional address format support
4. **Custom Formatting Rules**: Customer-specific formatting preferences

### Extensibility

The system is designed for easy extension:
- New locales can be added to the `FormattingService`
- Additional formatting methods can be implemented
- Custom formatting rules can be integrated through the Agreement system

## Error Handling

### Fallback Mechanisms

- **Unknown Locale**: Falls back to English formatting
- **Unknown Currency**: Uses generic currency symbol
- **Missing Agreement**: Uses basic `number_format` without locale awareness
- **Service Unavailable**: Graceful degradation to basic formatting

### Logging

The system logs formatting decisions and fallbacks for debugging and monitoring purposes.

## Performance Considerations

- **Caching**: Locale formatting rules are cached for performance
- **Lazy Loading**: Services are only instantiated when needed
- **Minimal Overhead**: Formatting adds minimal processing time to PDF generation

## Summary

The locale-based formatting system enhances the Invoice Engine's internationalization capabilities while maintaining backward compatibility and system reliability. The implementation follows Laravel best practices and integrates seamlessly with the existing event-driven architecture.