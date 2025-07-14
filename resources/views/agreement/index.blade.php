<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agreements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Manage Agreements</h1>

        <hr>

        <h2>Available Agreements</h2>
        @if(isset($agreements) && $agreements->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Billing Account</th>
                        <th>Strategy</th>
                        <th>Multiplier</th>
                        <th>VAT Rate</th>
                        <th>Currency</th>
                        <th>Locale</th>
                        <th>Rules</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agreements as $agreement)
                        <tr>
                            <td>{{ $agreement->id }}</td>
                            <td>{{ $agreement->billing_account }}</td>
                            <td>{{ $agreement->strategy }}</td>
                            <td>{{ $agreement->multiplier }}</td>
                            <td>{{ $agreement->vat_rate }}</td>
                            <td>{{ $agreement->currency }}</td>
                            <td>{{ $agreement->locale }}</td>
                            <td><pre>{{ json_encode($agreement->rules, JSON_PRETTY_PRINT) }}</pre></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No agreements found.</p>
        @endif

        <hr>

        <h2>Create New Agreement</h2>
        <form action="{{ route('agreement.store') }}" method="POST" class="mt-3">
            @csrf
            <div class="mb-3">
                <label for="billing_account" class="form-label">Billing Account:</label>
                <input type="text" id="billing_account" name="billing_account" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="strategy" class="form-label">Strategy:</label>
                <input type="text" id="strategy" name="strategy" value="standard" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="multiplier" class="form-label">Multiplier:</label>
                <input type="number" id="multiplier" name="multiplier" step="0.01" value="1.0" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="vat_rate" class="form-label">VAT Rate:</label>
                <input type="number" id="vat_rate" name="vat_rate" step="0.01" value="0.21" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="currency" class="form-label">Currency:</label>
                <input type="text" id="currency" name="currency" value="EUR" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="locale" class="form-label">Locale:</label>
                <input type="text" id="locale" name="locale" value="en" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rules" class="form-label">Rules (JSON):</label>
                <textarea id="rules" name="rules" class="form-control" required>{"base_charge_column":"price","surcharge_prefix":"surcharge_","surcharge_suffix":"_fee"}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Agreement</button>
        </form>
    </div>
</body>
</html>