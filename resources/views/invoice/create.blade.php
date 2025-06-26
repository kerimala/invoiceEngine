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
</body>
</html> 