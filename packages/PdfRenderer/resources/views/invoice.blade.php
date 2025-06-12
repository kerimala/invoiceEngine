<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Invoice</h1>
    <p>Original File: {{ $invoiceData['filePath'] }}</p>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoiceData['lines'] as $line)
                <tr>
                    <td>{{ $line['item'] }}</td>
                    <td>{{ $line['price'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 