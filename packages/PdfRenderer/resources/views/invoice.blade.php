<!DOCTYPE html>
<html{{ isset($languageConfig['rtl']) && $languageConfig['rtl'] ? ' dir="rtl"' : '' }}>
<head>
    <title>{{ __('invoice.title') }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .container { 
            width: 100%; 
            max-width: 800px; 
            margin: 0 auto;
        }
        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .logo-section {
            display: table-cell;
            width: 200px;
            vertical-align: top;
            text-align: right;
        }
        .logo-section img {
            max-width: 150px;
            max-height: 80px;
        }
        .company-info {
            display: table-cell;
            width: 300px;
            vertical-align: top;
            text-align: right;
            padding-left: 20px;
        }
        .company-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
        }
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .bill-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-meta {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .bill-to h4, .invoice-meta h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .bill-to p, .invoice-meta p {
            margin: 2px 0;
            font-size: 11px;
        }
        .invoice-title {
            text-align: center;
            margin: 30px 0;
        }
        .invoice-title h1 {
             margin: 0;
             font-size: 24px;
             font-weight: bold;
             text-transform: uppercase;
         }
         .items { 
             width: 100%; 
             border-collapse: collapse; 
             margin-bottom: 20px;
         }
         .items th {
             background-color: #f8f9fa;
             border: 1px solid #dee2e6;
             padding: 12px 8px;
             text-align: left;
             font-weight: bold;
             font-size: 11px;
         }
         .items td {
             border: 1px solid #dee2e6;
             padding: 8px;
             font-size: 11px;
         }
         .text-right { text-align: right; }
         .text-center { text-align: center; }
         .total-row {
             background-color: #f8f9fa;
             font-weight: bold;
         }
         .subtotal-section {
             width: 100%;
             margin-top: 20px;
         }
         .subtotal-table {
             width: 100%;
             border-collapse: collapse;
         }
         .subtotal-table td {
             padding: 5px 8px;
             font-size: 12px;
         }
         .subtotal-table .total-line {
             border-top: 2px solid #333;
             margin-top: 5px;
         }
         .subtotal-table .total-line td {
             padding-top: 8px;
             font-size: 13px;
         }
         .footer {
             margin-top: 40px;
             text-align: center;
             font-size: 10px;
             color: #666;
         }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo and Company Info -->
        <div class="header-section">
            <div class="logo-section">
                @if($agreement && $agreement->logo_path)
                    <img src="{{ $agreement->logo_path }}" alt="Company Logo">
                @endif
            </div>
            <div class="company-info">
                @if($agreement && $agreement->invoicing_company_name)
                    <h3>{{ $agreement->invoicing_company_name }}</h3>
                    @if($agreement->invoicing_company_address)
                        <p>{!! nl2br(e($agreement->invoicing_company_address)) !!}</p>
                    @endif
                    @if($agreement->invoicing_company_phone)
                        <p>T: {{ $agreement->invoicing_company_phone }}</p>
                    @endif
                    @if($agreement->invoicing_company_email)
                        <p>{{ $agreement->invoicing_company_email }}</p>
                    @endif
                    @if($agreement->invoicing_company_website)
                        <p>{{ $agreement->invoicing_company_website }}</p>
                    @endif
                    @if($agreement->invoicing_company_vat_number)
                        <p>{{ __('invoice.vat_number') }} {{ $agreement->invoicing_company_vat_number }}</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Customer Information -->
        <div class="invoice-details">
            <div class="bill-to">
                <h4>{{ $invoice['customer_name'] ?? 'Demo Klant B.V.' }}</h4>
                @if(isset($invoice['customer_address']))
                    <p>{!! nl2br(e($invoice['customer_address'])) !!}</p>
                @else
                    <p>Jan de Vries</p>
                    <p>Voorbeeldstraat 456</p>
                    <p>9876 ZX Teststad</p>
                    <p>Nederland</p>
                @endif
            </div>
            <div class="invoice-meta">
                <p><strong>{{ __('invoice.customer_number') }} {{ $invoice['customer_id'] ?? '97' }}</strong></p>
                <p><strong>{{ __('invoice.invoice_number') }} {{ $agreement && $agreement->invoice_number_prefix ? $agreement->invoice_number_prefix : '' }}{{ $invoice['invoice_id'] ?? '334867' }}</strong></p>
                <p><strong>{{ __('invoice.invoice_date') }} 
                    @if($agreement && $formatter)
                        {{ $formatter->formatDate(new \DateTime(), $agreement) }}
                    @else
                        {{ date('d-m-Y') }}
                    @endif
                </strong></p>
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h1>{{ strtoupper(__('invoice.title')) }}</h1>
        </div>

        <!-- Invoice Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th>{{ __('invoice.description') }}</th>
                    <th class="text-right">{{ __('invoice.price') }}</th>
                    <th class="text-right">{{ __('invoice.vat') }}</th>
                    <th class="text-right">{{ __('invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice['lines'] as $line)
                    <tr>
                        <td>{{ $line['description'] }}</td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['nett_total'], $agreement) }}
                            @else
                                {{ number_format($line['nett_total'], 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['vat_amount'], $agreement) }}
                            @else
                                {{ number_format($line['vat_amount'], 2, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['line_total'], $agreement) }}
                            @else
                                {{ number_format($line['line_total'], 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Subtotals Section -->
        <div class="subtotal-section">
            <table class="subtotal-table">
                <tr>
                    <td>{{ __('invoice.subtotal') }}</td>
                    <td class="text-right">
                        @if($agreement && $formatter)
                            {{ $formatter->formatPricing($invoice['subtotal'] ?? ($invoice['total_amount'] - ($invoice['vat_total'] ?? 0)), $agreement) }}
                        @else
                            {{ number_format(($invoice['subtotal'] ?? ($invoice['total_amount'] - ($invoice['vat_total'] ?? 0))), 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($agreement && $formatter)
                            {{ $formatter->formatPricing($invoice['vat_total'] ?? 0, $agreement) }}
                        @else
                            {{ number_format(($invoice['vat_total'] ?? 0), 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($agreement && $formatter)
                            {{ $formatter->formatPricing($invoice['total_amount'], $agreement) }}
                        @else
                            {{ number_format($invoice['total_amount'], 2, ',', '.') }}
                        @endif
                    </td>
                </tr>
                <tr class="total-line">
                    <td><strong>{{ __('invoice.invoice_amount') }}</strong></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">
                        <strong>
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($invoice['total_amount'], $agreement) }}
                            @else
                                {{ number_format($invoice['total_amount'], 2, ',', '.') }}
                            @endif
                        </strong>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment Terms -->
        <div style="margin-top: 30px; font-size: 11px;">
            <p><em>{{ __('invoice.payment_info') }}</em></p>
            <p><em>{{ __('invoice.collection_date') }}: 
                @if($agreement && $formatter)
                    {{ $formatter->formatDate(new \DateTime('+14 days'), $agreement) }}
                @else
                    {{ date('d-m-Y', strtotime('+14 days')) }}
                @endif
            </em></p>
        </div>

        <!-- Footer -->
        @if($agreement && $agreement->invoice_footer_text)
            <div class="footer">
                <p>{{ $agreement->invoice_footer_text }}</p>
            </div>
        @endif

        <!-- Bank Details Footer -->
        <div style="margin-top: 40px; font-size: 10px; text-align: center; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
            <p>KvK 12345678 BTW NL123456789B01 DEMO BANK 12.34.56.789 IBAN NL12DEMO1234567890 SWIFT/BIC DEMONL2A</p>
            <p>Op al onze offertes e.d. zijn onze Algemene Voorwaarden, Demo Logistics BV ingeschreven bij de</p>
            <p>KvK onder nummer 12345678, van toepassing. Deze kunt u terugvinden op de website www.demo-logistics.example.</p>
        </div>
    </div>
</body>
</html>