<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 90%; margin: auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .details, .items { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .details td, .items td, .items th { border: 1px solid #ddd; padding: 8px; }
        .items th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
        </div>

        <table class="details">
            <tr>
                <td><strong>Invoice ID:</strong></td>
                <td>{{ $invoice['invoice_id'] }}</td>
            </tr>
            <tr>
                <td><strong>Customer ID:</strong></td>
                <td>{{ $invoice['customer_id'] }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>{{ date('Y-m-d') }}</td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Nett</th>
                    <th class="text-right">VAT</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice['lines'] as $line)
                    <tr>
                        <td>
                            {{ $line['description'] }}
                        </td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['nett_total'], $agreement) }}
                            @else
                                {{ number_format($line['nett_total'], 2) }} {{ $line['currency'] }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['vat_amount'], $agreement) }}
                            @else
                                {{ number_format($line['vat_amount'], 2) }} {{ $line['currency'] }}
                            @endif
                        </td>
                        <td class="text-right">
                            @if($agreement && $formatter)
                                {{ $formatter->formatPricing($line['line_total'], $agreement) }}
                            @else
                                {{ number_format($line['line_total'], 2) }} {{ $line['currency'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right total">Total:</td>
                    <td class="text-right total">
                        @if($agreement && $formatter)
                            {{ $formatter->formatPricing($invoice['total_amount'], $agreement) }}
                        @else
                            {{ number_format($invoice['total_amount'], 2) }} {{ $invoice['currency'] }}
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>