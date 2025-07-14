<?php

namespace Packages\InvoiceParser\Tests;

use Illuminate\Support\Facades\Event;
use Packages\InvoiceParser\Services\InvoiceParserService;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Tests\TestCase;

class InvoiceParserServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $stubsDir = __DIR__ . '/stubs';
        if (!is_dir($stubsDir)) {
            mkdir($stubsDir);
        }
    }

    public function tearDown(): void
    {
        $stubsDir = __DIR__ . '/stubs';
        if (is_dir($stubsDir)) {
            $files = glob($stubsDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        parent::tearDown();
    }

    private function createSpreadsheetFile(string $filename, array $data, string $format = 'Xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        $path = __DIR__ . '/stubs/' . $filename;
        if ($format === 'Xlsx') {
            $writer = new Xlsx($spreadsheet);
        } else {
            $writer = new Csv($spreadsheet);
        }
        $writer->save($path);

        return $path;
    }

    public function test_parses_well_formed_xlsx_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $data = [
            ['header1', 'header2'],
            ['value1', 'value2'],
            ['value3', 'value4'],
        ];
        $filePath = $this->createSpreadsheetFile('invoice.xlsx', $data);

        $service->parse($filePath);

        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath 
                && $event->lineCount === 2
                && $event->parsedLines[0]['header1'] === 'value1';
        });
    }

    public function test_parses_well_formed_csv_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $data = [
            ['header1', 'header2'],
            ['value1', 'value2'],
            ['value3', 'value4'],
        ];
        $filePath = $this->createSpreadsheetFile('invoice.csv', $data, 'Csv');

        $service->parse($filePath);

        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath 
                && $event->lineCount === 2
                && $event->parsedLines[0]['header1'] === 'value1';
        });
    }

    public function test_parses_well_formed_xml_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/invoice.xml';
        $xml = "<invoices><line><header1>value1</header1><header2>value2</header2></line><line><header1>value3</header1><header2>value4</header2></line></invoices>";
        file_put_contents($filePath, $xml);
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && $event->lineCount === 2;
        });
    }

    public function test_throws_for_missing_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $service->parse('/nonexistent/file.csv');
    }

    public function test_throws_for_unsupported_file_extension()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/invoice.gif';
        file_put_contents($filePath, 'dummy');
        $service->parse($filePath);
    }

    public function test_parses_xlsx_with_pricing_data_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $data = [
            ['Billing Account', 'Weight Charge', 'Fuel Charge'],
            ['customer1', '100', '10'],
            ['customer2', '200', '20'],
        ];
        $filePath = $this->createSpreadsheetFile('invoice_with_pricing.xlsx', $data);

        $service->parse($filePath);

        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath
                && $event->lineCount === 2
                && $event->parsedLines[0]['Billing Account'] === 'customer1'
                && $event->parsedLines[0]['Weight Charge'] === '100'
                && $event->parsedLines[0]['Fuel Charge'] === '10';
        });
    }
    
    public function test_throws_for_malformed_xml()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/bad.xml';
        file_put_contents($filePath, '<invoices><line><header1>value1</header1></line>'); // missing closing tags
        $service->parse($filePath);
    }

    public function test_throws_for_empty_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/empty.csv';
        file_put_contents($filePath, '');
        $service->parse($filePath);
    }
}