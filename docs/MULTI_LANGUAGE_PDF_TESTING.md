# Multi-Language PDF Generation Testing

This document describes the comprehensive testing strategy for multi-language PDF generation in the Invoice Engine.

## Overview

The `PdfRendererMultiLanguageTest` class provides extensive testing coverage for PDF generation across all supported languages and locales. These tests ensure that the language-specific PDF rendering system works correctly for different locales, currencies, date formats, and translation keys.

## Test Coverage

### 1. Individual Language Tests

Each supported language has dedicated tests to verify PDF generation:

- **English (en)**: Tests USD currency with English locale formatting
- **German (de)**: Tests EUR currency with German locale formatting
- **French (fr)**: Tests EUR currency with French locale formatting
- **Dutch (nl)**: Tests EUR currency with Dutch locale formatting
- **Spanish (es)**: Tests EUR currency with Spanish locale formatting

### 2. Fallback Language Testing

- **Unsupported Locale Test**: Verifies that when an unsupported locale is provided, the system falls back to the configured fallback language
- **No Agreement Test**: Tests PDF generation without an agreement, ensuring the system uses the default fallback language

### 3. Currency Format Testing

Tests multiple currency formats across different locales:
- USD with English locale
- EUR with German, French, Dutch, and Spanish locales

This ensures proper currency symbol placement and number formatting according to locale conventions.

### 4. Language Priority Testing

Verifies the language selection priority hierarchy:
1. `invoice_language` (highest priority)
2. `locale` (medium priority)
3. `fallback_language` (lowest priority)
4. System default ('en') (ultimate fallback)

Three test cases validate this priority system:
- `invoice_language` overrides `locale` and `fallback_language`
- `locale` overrides `fallback_language` when `invoice_language` is null
- `fallback_language` is used when both `invoice_language` and `locale` are null

### 5. Complex Invoice Data Testing

Tests PDF generation with complex invoice data across all supported languages:
- Multiple invoice lines with different products/services
- Higher monetary amounts to test number formatting
- Extended date ranges
- Multiple agreement versions

## Test Data Structure

### Standard Test Invoice Data

```php
[
    'invoice_id' => 'INV-MULTI-LANG-001',
    'customer_id' => 'multilang@example.com',
    'lines' => [
        [
            'description' => 'Test Product A',
            'nett_total' => 1234.56,
            'vat_amount' => 234.87,
            'line_total' => 1469.43,
            'currency' => 'EUR',
            'agreement_version' => '1.0',
        ],
        // Additional lines...
    ],
    'total_amount' => 2145.22,
    'currency' => 'EUR',
    'invoice_date' => '2024-01-15',
    'due_date' => '2024-02-15'
]
```

### Complex Test Invoice Data

Includes premium software licenses, technical support, and training sessions with higher monetary values to test formatting edge cases.

## Supported Languages and Configurations

Based on `config/invoice-languages.php`:

| Language | Code | Date Format | Currency Position | RTL Support |
|----------|------|-------------|-------------------|-------------|
| English  | en   | m/d/Y       | before           | No          |
| German   | de   | d.m.Y       | after            | No          |
| French   | fr   | d/m/Y       | after            | No          |
| Dutch    | nl   | d-m-Y       | after            | No          |
| Spanish  | es   | d/m/Y       | after            | No          |
| Italian  | it   | d/m/Y       | after            | No          |
| Portuguese | pt | d/m/Y       | after            | No          |
| Polish   | pl   | d.m.Y       | after            | No          |
| Russian  | ru   | d.m.Y       | after            | No          |
| Chinese  | zh   | Y/m/d       | before           | No          |
| Japanese | ja   | Y/m/d       | before           | No          |

## Translation Keys Tested

The tests verify that the following translation keys are properly handled:

- `invoice.title` - Invoice header
- `invoice.customer_number` - Customer identification
- `invoice.invoice_number` - Invoice identification
- `invoice.invoice_date` - Invoice creation date
- `invoice.due_date` - Payment due date
- `invoice.description` - Item descriptions
- `invoice.price` - Item prices
- `invoice.vat` - VAT/tax information
- `invoice.total` - Total amounts
- `invoice.subtotal` - Subtotal calculations
- `invoice.vat_amount` - VAT amount
- `invoice.total_amount` - Final total
- `invoice.payment_terms` - Payment terms section
- `invoice.payment_info` - Payment collection information
- `invoice.collection_date` - Collection date
- `invoice.company_details` - Company information
- `invoice.customer_details` - Customer information
- `invoice.vat_number` - VAT registration number

## Formatting Verification

### Date Formatting

Each locale uses its specific date format:
- English: `01/15/2024`
- German: `15.01.2024`
- French: `15/01/2024`
- Dutch: `15-01-2024`
- Spanish: `15/01/2024`

### Currency Formatting

- **Before Symbol**: English, Chinese, Japanese (`$1,234.56`)
- **After Symbol**: German, French, Dutch, Spanish, etc. (`1.234,56 €`)

### Number Formatting

- **Decimal Separators**: Period (.) for English, Comma (,) for most European languages
- **Thousands Separators**: Comma (,) for English, Period (.) for German/Spanish, Space for French/Polish/Russian

## Error Handling

The tests verify proper error handling for:
- Null locale values in Agreement models
- Missing translation files
- Invalid date formats
- Unsupported currency codes

## Test Execution

### Running Multi-Language Tests

```bash
# Run all multi-language PDF tests
php artisan test packages/PdfRenderer/tests/PdfRendererMultiLanguageTest.php

# Run all PDF renderer tests
php artisan test packages/PdfRenderer/tests/

# Run specific test method
php artisan test packages/PdfRenderer/tests/PdfRendererMultiLanguageTest.php --filter=test_renders_pdf_with_german_locale
```

### Test Output

Successful test execution should show:
- 10 tests passed
- 38 assertions
- PDF files generated for each language test
- No errors or warnings

## File Generation

Each test generates PDF files in the `storage/app/invoices/` directory:
- `INV-EN-001.pdf` (English)
- `INV-DE-001.pdf` (German)
- `INV-FR-001.pdf` (French)
- `INV-NL-001.pdf` (Dutch)
- `INV-ES-001.pdf` (Spanish)
- Additional files for complex and priority tests

## Dependencies

The multi-language tests depend on:
- Laravel's localization system
- Translation files in `resources/lang/`
- Language configuration in `config/invoice-languages.php`
- FormattingService for locale-specific formatting
- PdfRenderer service for PDF generation
- Dompdf library for PDF creation

## Future Enhancements

Potential improvements to the testing suite:

1. **RTL Language Support**: Add tests for right-to-left languages when implemented
2. **PDF Content Verification**: Parse generated PDFs to verify actual content
3. **Performance Testing**: Measure PDF generation time across languages
4. **Visual Regression Testing**: Compare PDF outputs for visual consistency
5. **Accessibility Testing**: Verify PDF accessibility features
6. **Font Testing**: Ensure proper font rendering for different character sets

## Troubleshooting

### Common Issues

1. **Null Locale Errors**: Ensure FormattingService methods handle null locale values
2. **Missing Translation Files**: Verify all language files exist in `resources/lang/`
3. **PDF Generation Failures**: Check Dompdf configuration and temp directory permissions
4. **Currency Formatting Issues**: Verify currency symbols and positioning in FormattingService

### Debug Tips

- Enable Laravel logging to see detailed PDF generation logs
- Check `storage/logs/laravel.log` for error details
- Verify file permissions on `storage/app/invoices/` directory
- Test individual language configurations in isolation

This comprehensive testing approach ensures robust multi-language PDF generation across all supported locales and use cases.