# Language-Specific PDF Rendering Implementation

## Overview

This document describes the implementation of language-specific PDF rendering for the Invoice Engine, allowing invoices to be generated in multiple languages with proper locale-specific formatting.

## Features Implemented

### 1. Multi-Language Support
- **Supported Languages**: English (en), German (de), French (fr), Dutch (nl), Spanish (es)
- **Translation Files**: Located in `resources/lang/{locale}/invoice.php`
- **RTL Support**: Configuration for Right-to-Left languages

### 2. Database Schema Updates
- Added `invoice_language` field to `agreements` table (nullable, 5 characters)
- Added `fallback_language` field to `agreements` table (default: 'en', 5 characters)
- Migration: `2025_07_14_135032_add_language_fields_to_agreements_table.php`

### 3. Configuration
- **Language Config**: `config/invoice-languages.php`
  - Language metadata (name, RTL support, date format, currency position)
  - Fallback language configuration
  - Auto-detection settings

### 4. Enhanced Services

#### PdfRenderer Service
- **Locale Detection**: Determines appropriate locale from Agreement object
- **Dynamic Language Loading**: Sets application locale during rendering
- **Template Integration**: Passes language configuration to Blade templates
- **Locale Restoration**: Restores original locale after rendering

#### FormattingService (UnitConverter Package)
- **Date Formatting**: New methods `formatDate()` and `formatDateWithLocale()`
- **Locale-Specific Formats**:
  - English: `mm/dd/yyyy`
  - German: `dd.mm.yyyy`
  - French: `dd/mm/yyyy`
  - Dutch: `dd-mm-yyyy`
  - Spanish: `dd/mm/yyyy`

### 5. Template Updates
- **Translation Integration**: Uses `__('invoice.key')` for all text elements
- **RTL Support**: Conditional `dir="rtl"` attribute
- **Dynamic Date Formatting**: Integrates with FormattingService
- **Locale-Aware Rendering**: Respects language configuration

## Usage

### Setting Language for Agreement
```php
$agreement = Agreement::create([
    'customer_id' => 'CUST001',
    'invoice_language' => 'de',  // German
    'fallback_language' => 'en', // English fallback
    // ... other fields
]);
```

### Rendering PDF with Language Support
```php
$renderer = new PdfRenderer();
$pdfPath = $renderer->render($invoiceData, $agreement);
// PDF will be rendered in German with German date formatting
```

### Date Formatting
```php
$formatter = app(FormattingService::class);
$formattedDate = $formatter->formatDate('2024-01-15', $agreement);
// Returns: "15.01.2024" for German locale
```

## Language Fallback Logic

1. **Primary**: `agreement.invoice_language`
2. **Secondary**: `agreement.locale`
3. **Tertiary**: `agreement.fallback_language`
4. **Default**: 'en' (English)

## File Structure

```
resources/lang/
├── en/invoice.php          # English translations
├── de/invoice.php          # German translations
├── fr/invoice.php          # French translations
├── nl/invoice.php          # Dutch translations
└── es/invoice.php          # Spanish translations

config/
└── invoice-languages.php   # Language configuration

database/migrations/
└── 2025_07_14_135032_add_language_fields_to_agreements_table.php

tests/Feature/Services/
└── PdfRendererLanguageTest.php  # Language-specific PDF tests

packages/UnitConverter/tests/
└── FormattingServiceTest.php    # Date formatting tests
```

## Testing

- **PDF Rendering Tests**: Verify language-specific PDF generation
- **Date Formatting Tests**: Validate locale-specific date formats
- **Fallback Tests**: Ensure proper fallback behavior
- **Integration Tests**: End-to-end language support validation

## Translation Keys

Key translation strings in `invoice.php`:
- `title`: "Invoice"
- `vat_number`: "VAT Number"
- `customer_number`: "Customer Number"
- `invoice_number`: "Invoice Number"
- `invoice_date`: "Invoice Date"
- `description`: "Description"
- `quantity`: "Quantity"
- `unit_price`: "Unit Price"
- `total`: "Total"
- `subtotal`: "Subtotal"
- `vat`: "VAT"
- `invoice_amount`: "Invoice Amount"
- `payment_terms`: "Payment Terms"
- `payment_info`: "Payment Information"
- `collection_date`: "Collection Date"

## Future Enhancements

- Additional language support
- Region-specific address formatting
- Custom date format configurations