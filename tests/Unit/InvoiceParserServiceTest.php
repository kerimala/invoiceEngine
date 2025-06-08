<?php
// tests/Unit/InvoiceParserServiceTest.php

use Tests\TestCase;
uses(TestCase::class);

use App\Services\InvoiceParserService;
use Exception;

beforeEach(function () {
    // Nothing special to set up before each test.
});

it('throws an exception if the file does not exist', function () {
    $svc = new InvoiceParserService();

    $badPath = base_path('invoices/nonexistent-invoice.txt');
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('file not found');

    $svc->parse($badPath);
});

it('throws if the file has an unsupported extension', function () {
    $svc = new InvoiceParserService();

    $badFile = makeTempFile('invoice.jpg', 'NOT AN INVOICE');

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('unsupported file format');

    $svc->parse($badFile);

    cleanTempFile($badFile);
});

it('throws if required header lines are missing in plain-text invoice', function () {
    $svc = new InvoiceParserService();

    $badText = makeTempFile('bad-invoice.txt', <<<TEXT
customer_id: CUST-001
// Missing invoice_number
date: 2025-05-01

item: A, quantity: 1, unit_price: 5.00
TEXT
    );
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('missing required header');

    $svc->parse($badText);

    cleanTempFile($badText);
});

it('parses a well-formed plain-text invoice correctly', function () {
    $svc = new InvoiceParserService();

    $goodText = makeTempFile('good-invoice.txt', <<<TEXT
invoice_number: INV-202505
customer_id: CUST-202
date: 2025-05-02

item: Widget A, quantity: 2, unit_price: 10.00
item: Widget B, quantity: 1, unit_price: 20.50
TEXT
    );

    $data = $svc->parse($goodText);
    expect($data)->toBeArray()->toHaveKeys(['invoice_number', 'customer_id', 'date', 'line_items']);
    expect($data['invoice_number'])->toBe('INV-202505');
    expect($data['customer_id'])->toBe('CUST-202');
    expect($data['date'])->toBe('2025-05-02');
    expect($data['line_items'])->toHaveCount(2);
    expect($data['line_items'][0])->toMatchArray([
        'description' => 'Widget A',
        'quantity'    => 2,
        'unit_price'  => 10.00,
    ]);
    expect($data['line_items'][1])->toMatchArray([
        'description' => 'Widget B',
        'quantity'    => 1,
        'unit_price'  => 20.50,
    ]);

    cleanTempFile($goodText);
});

it('parses a well-formed CSV invoice correctly', function () {
    $svc = new InvoiceParserService();

    $csvData = implode("\n", [
        'invoice_number,customer_id,date',
        'INV-CSV-001,CUST-CSV,2025-05-03',
        '',
        'description,quantity,unit_price',
        'Widget C,3,15.00',
        'Widget D,2,7.50',
    ]);
    $csvFile = makeTempFile('invoice.csv', $csvData);

    $data = $svc->parse($csvFile);
    expect($data['invoice_number'])->toBe('INV-CSV-001');
    expect($data['customer_id'])->toBe('CUST-CSV');
    expect($data['date'])->toBe('2025-05-03');
    expect($data['line_items'])->toHaveCount(2);
    expect($data['line_items'][0])->toMatchArray([
        'description' => 'Widget C',
        'quantity'    => 3,
        'unit_price'  => 15.00,
    ]);
    expect($data['line_items'][1])->toMatchArray([
        'description' => 'Widget D',
        'quantity'    => 2,
        'unit_price'  => 7.50,
    ]);

    cleanTempFile($csvFile);
});

it('parses a well-formed XML invoice correctly', function () {
    $svc = new InvoiceParserService();

    $xmlContent = <<<XML
<?xml version="1.0"?>
<invoice>
  <invoice_number>INV-XML-001</invoice_number>
  <customer_id>CUST-XML</customer_id>
  <date>2025-05-04</date>
  <line_items>
    <item>
      <description>Widget E</description>
      <quantity>5</quantity>
      <unit_price>12.00</unit_price>
    </item>
    <item>
      <description>Widget F</description>
      <quantity>1</quantity>
      <unit_price>30.00</unit_price>
    </item>
  </line_items>
</invoice>
XML;
    $xmlFile = makeTempFile('invoice.xml', $xmlContent);

    $data = $svc->parse($xmlFile);
    expect($data['invoice_number'])->toBe('INV-XML-001');
    expect($data['customer_id'])->toBe('CUST-XML');
    expect($data['date'])->toBe('2025-05-04');
    expect($data['line_items'])->toHaveCount(2);
    expect($data['line_items'][0])->toMatchArray([
        'description' => 'Widget E',
        'quantity'    => 5,
        'unit_price'  => 12.00,
    ]);
    expect($data['line_items'][1])->toMatchArray([
        'description' => 'Widget F',
        'quantity'    => 1,
        'unit_price'  => 30.00,
    ]);

    cleanTempFile($xmlFile);
});

it('throws for malformed CSV content', function () {
    $svc = new InvoiceParserService();

    $csvData = implode("\n", [
        'foo,bar,baz',
        'x,y,z',
    ]);
    $badCsv = makeTempFile('bad-invoice.csv', $csvData);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('missing required header');

    $svc->parse($badCsv);

    cleanTempFile($badCsv);
});

it('throws for malformed XML content', function () {
    $svc = new InvoiceParserService();

    $xmlData = <<<XML
<?xml version="1.0"?>
<invoice>
  <customer_id>CUST-XML</customer_id>
  <date>2025-05-04</date>
  <line_items>
    <item>
      <description>Widget E</description>
      <quantity>5</quantity>
      <unit_price>12.00</unit_price>
    </item>
  </line_items>
</invoice>
XML;
    $badXml = makeTempFile('bad-invoice.xml', $xmlData);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('missing required header');

    $svc->parse($badXml);

    cleanTempFile($badXml);
});

it('throws for unsupported Excel format until implemented', function () {
    $svc = new InvoiceParserService();

    $excelFile = makeTempFile('invoice.xlsx', 'NOT REAL EXCEL');

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Excel parsing not implemented');

    $svc->parse($excelFile);

    cleanTempFile($excelFile);
});

it('throws for an empty file', function () {
    $svc = new InvoiceParserService();

    $empty = makeTempFile('empty.txt', '');
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('missing required header');
    $svc->parse($empty);
    cleanTempFile($empty);
});

it('handles uppercase header labels in plain-text', function () {
    $svc = new InvoiceParserService();

    $file = makeTempFile('mixedcase.txt', <<<TEXT
INVOICE_NUMBER: INV-123
CUSTOMER_ID: CUST-001
DATE: 2025-05-02

ITEM: A, QUANTITY: 2, UNIT_PRICE: 5.00
TEXT
    );
    $data = $svc->parse($file);
    expect($data['invoice_number'])->toBe('INV-123');
    expect($data['customer_id'])->toBe('CUST-001');
    expect($data['date'])->toBe('2025-05-02');
    cleanTempFile($file);
});

it('throws when date is not in YYYY-MM-DD', function () {
    $svc = new InvoiceParserService();

    $invalidDate = makeTempFile('invalid-date.txt', <<<TEXT
invoice_number: INV-001
customer_id: CUST-001
date: 05/03/2025

item: A, quantity: 1, unit_price: 10.00
TEXT
    );
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('invalid date format');
    $svc->parse($invalidDate);
    cleanTempFile($invalidDate);
});