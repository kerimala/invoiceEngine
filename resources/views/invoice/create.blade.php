<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Invoice</title>
</head>
<body>
    <h1>Upload Invoice</h1>
    <form action="{{ route('invoice.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="invoice_file">
        <button type="submit">Upload</button>
    </form>

    <hr>

    <h1>Create New Agreement</h1>
    <form action="{{ route('agreement.store') }}" method="POST">
        @csrf
        <div>
            <label for="billing_account">Billing Account:</label>
            <input type="text" id="billing_account" name="billing_account" required>
        </div>
        <div>
            <label for="strategy">Strategy:</label>
            <input type="text" id="strategy" name="strategy" value="standard" required>
        </div>
        <div>
            <label for="multiplier">Multiplier:</label>
            <input type="number" id="multiplier" name="multiplier" step="0.01" value="1.0" required>
        </div>
        <div>
            <label for="vat_rate">VAT Rate:</label>
            <input type="number" id="vat_rate" name="vat_rate" step="0.01" value="0.21" required>
        </div>
        <div>
            <label for="currency">Currency:</label>
            <input type="text" id="currency" name="currency" value="EUR" required>
        </div>
        <div>
            <label for="language">Language:</label>
            <input type="text" id="language" name="language" value="en" required>
        </div>
        <div>
            <label for="rules">Rules (JSON):</label>
            <textarea id="rules" name="rules" required>{"base_charge_column":"price","surcharge_prefix":"surcharge_","surcharge_suffix":"_fee"}</textarea>
        </div>
        <button type="submit">Create Agreement</button>
    </form>
</body>
</html>