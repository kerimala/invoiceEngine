<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Database Viewer</h1>

        <h2 class="mt-5">Agreements</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer ID</th>
                    <th>Version</th>
                    <th>Strategy</th>
                    <th>Multiplier</th>
                    <th>VAT Rate</th>
                    <th>Currency</th>
                    <th>Language</th>
                    <th>Rules</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agreements as $agreement)
                    <tr>
                        <td>{{ $agreement->id }}</td>
                        <td>{{ $agreement->customer_id }}</td>
                        <td>{{ $agreement->version }}</td>
                        <td>{{ $agreement->strategy }}</td>
                        <td>{{ $agreement->multiplier }}</td>
                        <td>{{ $agreement->vat_rate }}</td>
                        <td>{{ $agreement->currency }}</td>
                        <td>{{ $agreement->language }}</td>
                        <td><pre>{{ json_encode($agreement->rules, JSON_PRETTY_PRINT) }}</pre></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2 class="mt-5">Enriched Invoice Lines</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Raw Line</th>
                    <th>Nett Total</th>
                    <th>VAT Amount</th>
                    <th>Line Total</th>
                    <th>Currency</th>
                    <th>Agreement Version</th>
                    <th>Agreement Type</th>
                    <th>Pricing Strategy</th>
                    <th>Processing Metadata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrichedInvoiceLines as $line)
                    <tr>
                        <td>{{ $line->id }}</td>
                        <td><pre>{{ json_encode($line->raw_line, JSON_PRETTY_PRINT) }}</pre></td>
                        <td>{{ $line->nett_total }}</td>
                        <td>{{ $line->vat_amount }}</td>
                        <td>{{ $line->line_total }}</td>
                        <td>{{ $line->currency }}</td>
                        <td>{{ $line->agreement_version }}</td>
                        <td>{{ $line->agreement_type }}</td>
                        <td>{{ $line->pricing_strategy }}</td>
                        <td><pre>{{ json_encode($line->processing_metadata, JSON_PRETTY_PRINT) }}</pre></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>