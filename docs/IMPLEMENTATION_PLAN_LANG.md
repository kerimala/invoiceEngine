# Implementation Plan: Locale-Based PDF Rendering

Based on the current system analysis, this document outlines a comprehensive implementation plan for managing PDF rendering with specific languages based on the agreement's locale setting.

## Current System Overview

The system already has a solid foundation:
- **Agreement Model**: Contains `locale` and `currency` fields
- **FormattingService**: Handles locale-aware number/currency formatting
- **PdfRenderer**: Accepts Agreement objects and uses FormattingService
- **Multi-language Support**: Currently supports 11 locales (en, de, fr, es, it, nl, pt, pl, ru, zh, ja)

## Implementation Plan

### Phase 1: Language Translation System

#### 1.1 Create Translation Files
```
resources/lang/
├── en/
│   └── invoice.php
├── de/
│   └── invoice.php
├── fr/
│   └── invoice.php
└── nl/
    └── invoice.php
```

**Translation Keys to Include:**
- Invoice labels ("FACTUUR", "INVOICE", "FACTURE")
- Field labels ("Klantnummer", "Customer Number", "Numéro client")
- Table headers ("Omschrijving", "Description", "Description")
- Payment terms and footer text

**Example Translation File (`resources/lang/en/invoice.php`):**
```php
<?php

return [
    'title' => 'INVOICE',
    'customer_number' => 'Customer Number',
    'invoice_number' => 'Invoice Number',
    'invoice_date' => 'Invoice Date',
    'description' => 'Description',
    'price' => 'Price',
    'vat' => 'VAT',
    'total' => 'Total',
    'subtotal' => 'Subtotal',
    'vat_amount' => 'VAT Amount',
    'total_amount' => 'Total Amount',
    'payment_terms' => 'Payment Terms',
    'due_date' => 'Due Date',
    'company_details' => 'Company Details',
    'customer_details' => 'Customer Details',
];
```

**German Translation File (`resources/lang/de/invoice.php`):**
```php
<?php

return [
    'title' => 'RECHNUNG',
    'customer_number' => 'Kundennummer',
    'invoice_number' => 'Rechnungsnummer',
    'invoice_date' => 'Rechnungsdatum',
    'description' => 'Beschreibung',
    'price' => 'Preis',
    'vat' => 'MwSt.',
    'total' => 'Gesamt',
    'subtotal' => 'Zwischensumme',
    'vat_amount' => 'MwSt.-Betrag',
    'total_amount' => 'Gesamtbetrag',
    'payment_terms' => 'Zahlungsbedingungen',
    'due_date' => 'Fälligkeitsdatum',
    'company_details' => 'Firmendetails',
    'customer_details' => 'Kundendetails',
];
```

#### 1.2 Update Agreement Model
Add language-specific fields:
```php
protected $fillable = [
    // ... existing fields
    'invoice_language', // Primary language for invoice text
    'fallback_language', // Fallback if primary not available
];
```

### Phase 2: Enhanced PdfRenderer

#### 2.1 Locale-Aware Rendering
Update `PdfRenderer::render()` method:
```php
public function render(array $invoiceData, ?Agreement $agreement = null): string
{
    // Set application locale based on agreement
    if ($agreement && $agreement->locale) {
        $language = $agreement->invoice_language ?? $agreement->locale;
        App::setLocale($language);
    }
    
    // Pass locale-specific data to template
    $html = View::make('pdf-renderer::invoice', [
        'invoice' => $invoiceData,
        'agreement' => $agreement,
        'formatter' => $this->formattingService,
        'locale' => $agreement->locale ?? 'en',
        'language' => $agreement->invoice_language ?? $agreement->locale ?? 'en'
    ])->render();
    
    // ... rest of PDF generation
}
```

#### 2.2 Template Localization
Update `invoice.blade.php` to use translations:
```php
<!-- Replace hardcoded Dutch text -->
<h1>{{ __('invoice.title') }}</h1> <!-- "FACTUUR" / "INVOICE" / "FACTURE" -->
<div>{{ __('invoice.customer_number') }}: {{ $invoice['customer_id'] }}</div>
<div>{{ __('invoice.invoice_number') }}: {{ $invoice['invoice_number'] }}</div>
<div>{{ __('invoice.invoice_date') }}: {{ $invoice['invoice_date'] }}</div>

<!-- Table headers -->
<th>{{ __('invoice.description') }}</th>
<th>{{ __('invoice.price') }}</th>
<th>{{ __('invoice.vat') }}</th>
<th>{{ __('invoice.total') }}</th>

<!-- Formatted amounts using existing FormattingService -->
@if(isset($formatter) && isset($agreement))
    {{ $formatter->formatPricing($invoice['nett_total'], $agreement) }}
@else
    {{ number_format($invoice['nett_total'], 2) }}
@endif
```

### Phase 3: Enhanced FormattingService

#### 3.1 Date Formatting
Add date formatting methods to FormattingService:
```php
/**
 * Format date based on locale from agreement
 */
public function formatDate(\DateTime $date, Agreement $agreement): string
{
    $locale = $this->getLocale($agreement->locale);
    
    if (class_exists('\IntlDateFormatter')) {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE
        );
        return $formatter->format($date);
    }
    
    // Fallback for systems without Intl extension
    return $date->format($this->getDateFormat($agreement->locale));
}

/**
 * Get date format pattern for locale
 */
private function getDateFormat(string $locale): string
{
    $formats = [
        'en' => 'M j, Y',      // Jan 15, 2024
        'de' => 'd.m.Y',       // 15.01.2024
        'fr' => 'd/m/Y',       // 15/01/2024
        'nl' => 'd-m-Y',       // 15-01-2024
        'es' => 'd/m/Y',       // 15/01/2024
        'it' => 'd/m/Y',       // 15/01/2024
    ];
    
    return $formats[$locale] ?? 'Y-m-d';
}
```

#### 3.2 Address Formatting
Add region-specific address formatting:
```php
/**
 * Format address based on locale conventions
 */
public function formatAddress(array $address, Agreement $agreement): string
{
    // Different address formats for different regions
    switch ($agreement->locale) {
        case 'de':
        case 'nl':
            return "{$address['name']}\n{$address['street']}\n{$address['postal_code']} {$address['city']}\n{$address['country']}";
        case 'en':
            return "{$address['name']}\n{$address['street']}\n{$address['city']}, {$address['postal_code']}\n{$address['country']}";
        case 'fr':
            return "{$address['name']}\n{$address['street']}\n{$address['postal_code']} {$address['city']}\n{$address['country']}";
        default:
            return "{$address['name']}\n{$address['street']}\n{$address['city']} {$address['postal_code']}\n{$address['country']}";
    }
}
```

### Phase 4: Configuration Management

#### 4.1 Language Configuration
Create configuration file `config/invoice-languages.php`:
```php
<?php

return [
    'supported_languages' => [
        'en' => [
            'name' => 'English',
            'rtl' => false,
            'date_format' => 'M j, Y',
            'currency_position' => 'before'
        ],
        'de' => [
            'name' => 'Deutsch',
            'rtl' => false,
            'date_format' => 'd.m.Y',
            'currency_position' => 'after'
        ],
        'fr' => [
            'name' => 'Français',
            'rtl' => false,
            'date_format' => 'd/m/Y',
            'currency_position' => 'after'
        ],
        'nl' => [
            'name' => 'Nederlands',
            'rtl' => false,
            'date_format' => 'd-m-Y',
            'currency_position' => 'after'
        ],
        'ar' => [
            'name' => 'العربية',
            'rtl' => true,
            'date_format' => 'd/m/Y',
            'currency_position' => 'after'
        ], // Future RTL support
    ],
    'fallback_language' => 'en',
    'auto_detect' => true,
];
```

#### 4.2 Template Variants
For complex layouts, create language-specific templates:
```
packages/PdfRenderer/resources/views/
├── invoice.blade.php (default)
├── invoice-rtl.blade.php (for RTL languages)
└── partials/
    ├── header.blade.php
    ├── customer-details.blade.php
    ├── invoice-items.blade.php
    └── footer.blade.php
```

### Phase 5: Testing Strategy

#### 5.1 Unit Tests
```php
// Test locale-specific formatting
test('formats invoice with German locale', function () {
    $agreement = Agreement::factory()->create([
        'locale' => 'de',
        'currency' => 'EUR',
        'invoice_language' => 'de'
    ]);
    
    $invoiceData = [
        'invoice_id' => 'TEST-001',
        'nett_total' => 1234.56,
        'vat_amount' => 234.67,
        'total_amount' => 1469.23,
    ];
    
    $pdfRenderer = app(PdfRenderer::class);
    $pdfPath = $pdfRenderer->render($invoiceData, $agreement);
    
    $pdfContent = Storage::get($pdfPath);
    
    expect($pdfContent)->toContain('RECHNUNG'); // German for invoice
    expect($pdfContent)->toContain('1.234,56 €'); // German number format
    expect($pdfContent)->toContain('Kundennummer'); // German for customer number
});

test('formats invoice with English locale', function () {
    $agreement = Agreement::factory()->create([
        'locale' => 'en',
        'currency' => 'USD',
        'invoice_language' => 'en'
    ]);
    
    $invoiceData = [
        'invoice_id' => 'TEST-002',
        'nett_total' => 1234.56,
        'vat_amount' => 234.67,
        'total_amount' => 1469.23,
    ];
    
    $pdfRenderer = app(PdfRenderer::class);
    $pdfPath = $pdfRenderer->render($invoiceData, $agreement);
    
    $pdfContent = Storage::get($pdfPath);
    
    expect($pdfContent)->toContain('INVOICE'); // English
    expect($pdfContent)->toContain('$1,234.56'); // English number format
    expect($pdfContent)->toContain('Customer Number'); // English
});

test('falls back to default language when translation missing', function () {
    $agreement = Agreement::factory()->create([
        'locale' => 'unsupported',
        'currency' => 'EUR',
        'invoice_language' => 'unsupported'
    ]);
    
    $invoiceData = ['invoice_id' => 'TEST-003'];
    
    $pdfRenderer = app(PdfRenderer::class);
    $pdfPath = $pdfRenderer->render($invoiceData, $agreement);
    
    $pdfContent = Storage::get($pdfPath);
    
    expect($pdfContent)->toContain('INVOICE'); // Falls back to English
});
```

#### 5.2 Integration Tests
```php
test('processes full invoice with locale formatting', function () {
    // Test complete pipeline with different locales
    $locales = ['en', 'de', 'fr', 'nl'];
    
    foreach ($locales as $locale) {
        $agreement = Agreement::factory()->create([
            'locale' => $locale,
            'currency' => 'EUR',
            'invoice_language' => $locale
        ]);
        
        // Process invoice through full pipeline
        $result = $this->processInvoiceWithLocale($agreement);
        
        expect($result['pdf_path'])->toBeString();
        expect($result['locale_applied'])->toBe($locale);
    }
});
```

### Phase 6: Migration Strategy

#### 6.1 Database Migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->string('invoice_language', 5)->nullable()->after('locale');
            $table->string('fallback_language', 5)->default('en')->after('invoice_language');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['invoice_language', 'fallback_language']);
        });
    }
};
```

#### 6.2 Data Migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Agreement;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing agreements to use invoice_language
        Agreement::whereNull('invoice_language')
            ->update(['invoice_language' => DB::raw('locale')]);
    }

    public function down(): void
    {
        // Rollback not needed as we're just setting defaults
    }
};
```

### Phase 7: API Enhancements

#### 7.1 Language Detection Service
Create a service for automatic language detection:
```php
<?php

namespace App\Services;

use App\Models\Agreement;
use Illuminate\Http\Request;

class LanguageDetectionService
{
    public function detectLanguage(Agreement $agreement, ?Request $request = null): string
    {
        // Priority order for language detection
        
        // 1. Explicit invoice language setting
        if ($agreement->invoice_language) {
            return $agreement->invoice_language;
        }
        
        // 2. Agreement locale
        if ($agreement->locale) {
            return $agreement->locale;
        }
        
        // 3. Browser language (if web request)
        if ($request && $request->hasHeader('Accept-Language')) {
            $browserLang = $this->parseBrowserLanguage($request->header('Accept-Language'));
            if ($this->isSupported($browserLang)) {
                return $browserLang;
            }
        }
        
        // 4. Fallback to default
        return config('invoice-languages.fallback_language', 'en');
    }
    
    private function parseBrowserLanguage(string $acceptLanguage): string
    {
        $languages = explode(',', $acceptLanguage);
        $primary = explode(';', $languages[0])[0];
        return substr($primary, 0, 2); // Get language code only
    }
    
    private function isSupported(string $language): bool
    {
        return array_key_exists($language, config('invoice-languages.supported_languages', []));
    }
}
```

#### 7.2 Preview Endpoints
Create endpoints for language preview:
```php
// routes/web.php
Route::get('/preview-invoice/{id}/language/{lang}', [InvoiceController::class, 'previewLanguage'])
    ->name('invoice.preview.language');

// app/Http/Controllers/InvoiceController.php
public function previewLanguage(string $id, string $lang)
{
    $agreement = Agreement::findOrFail($id);
    
    // Temporarily override language for preview
    $originalLanguage = $agreement->invoice_language;
    $agreement->invoice_language = $lang;
    
    $pdfRenderer = app(PdfRenderer::class);
    $pdfPath = $pdfRenderer->render($this->getSampleInvoiceData(), $agreement);
    
    // Restore original language
    $agreement->invoice_language = $originalLanguage;
    
    return response()->file(storage_path('app/' . $pdfPath));
}
```

## Implementation Priority

### High Priority (Phase 1-2)
- Translation files and template updates
- Basic locale-aware PDF rendering
- Core language switching functionality

### Medium Priority (Phase 3-4)
- Enhanced formatting (dates, addresses)
- Configuration management
- Comprehensive testing

### Low Priority (Phase 5-7)
- Advanced features (RTL support)
- API enhancements
- Language detection automation

## Benefits

- **Scalable**: Easy to add new languages through translation files
- **Maintainable**: Centralized translation management
- **Flexible**: Per-customer language preferences via Agreement model
- **Backward Compatible**: Existing functionality preserved
- **Performance**: Minimal overhead with proper caching
- **Testable**: Comprehensive test coverage for all locales

## Technical Considerations

### Performance
- Cache compiled translation files
- Lazy load language resources
- Optimize PDF generation for different locales

### Security
- Validate language codes to prevent injection
- Sanitize translation content
- Secure file access for language resources

### Maintenance
- Version control for translation files
- Translation validation tools
- Automated testing for all language combinations

This implementation plan leverages the existing locale-aware formatting system and extends it with comprehensive language support while maintaining the robust architecture already built in the Invoice Engine.