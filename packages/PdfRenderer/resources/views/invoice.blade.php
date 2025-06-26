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
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice['lines'] as $line)
                    <tr>
                        <td>
                            Line Item (Agreement: {{ $line['agreement_version'] }})
                            <ul>
                                @foreach ($line as $key => $value)
                                    @if (!in_array($key, ['line_total', 'agreement_version', 'currency', 'last_line']))
                                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-right">{{ number_format($line['line_total'], 2) }} {{ $line['currency'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-right total">Total:</td>
                    <td class="text-right total">{{ number_format($invoice['total_amount'], 2) }} {{ $invoice['currency'] }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html> 