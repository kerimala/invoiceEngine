<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->getInvoiceId() }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .invoice-details {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice #{{ $invoice->getInvoiceId() }}</h1>
        <p>Thank you for your business!</p>
    </div>

    <div class="invoice-details">
        <h3>Invoice Details</h3>
        <p><strong>Invoice ID:</strong> {{ $invoice->getInvoiceId() }}</p>
        <p><strong>Customer ID:</strong> {{ $invoice->getCustomerId() }}</p>
        <p><strong>Total Amount:</strong> {{ number_format($invoice->getTotalAmount(), 2) }} {{ $invoice->getCurrency() }}</p>
        
        @if($invoice->getLines() && count($invoice->getLines()) > 0)
        <h4>Invoice Lines:</h4>
        <ul>
            @foreach($invoice->getLines() as $line)
            <li>
                {{ $line->getDescription() }} - 
                Qty: {{ $line->getQuantity() }} × 
                {{ number_format($line->getUnitPrice(), 2) }} {{ $line->getCurrency() }} = 
                {{ number_format($line->getTotal(), 2) }} {{ $line->getCurrency() }}
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    <p>Please find your invoice attached as a PDF document.</p>

    <div class="footer">
        <p>If you have any questions about this invoice, please contact us.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>