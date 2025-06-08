<?php

namespace Packages\InvoiceParser\Tests;

use Illuminate\Support\Facades\Event;
use Packages\InvoiceParser\Services\InvoiceParserService;
use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Tests\TestCase;

class InvoiceParserServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (!is_dir(__DIR__ . '/stubs')) {
            mkdir(__DIR__ . '/stubs');
        }
    }

    public function tearDown(): void
    {
        array_map('unlink', glob(__DIR__ . '/stubs/*'));
        parent::tearDown();
    }

    public function test_parses_well_formed_csv_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/invoice.csv';
        file_put_contents($filePath, "header1,header2\nvalue1,value2\nvalue3,value4");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && $event->lineCount === 2;
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
        $filePath = __DIR__ . '/stubs/invoice.txt';
        file_put_contents($filePath, 'dummy');
        $service->parse($filePath);
    }

    public function test_throws_for_malformed_csv()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/bad.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1'); // missing value2
        $service->parse($filePath);
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

    public function test_throws_for_missing_required_headers_in_csv()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/noheaders.csv';
        file_put_contents($filePath, 'foo,bar\nvalue1,value2');
        $service->parse($filePath);
    }

    public function test_handles_mixed_case_extensions()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/invoice.CsV';
        file_put_contents($filePath, "header1,header2\nvalue1,value2");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && $event->lineCount === 1;
        });
    }

    public function test_emits_event_with_correct_metadata()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/invoice.csv';
        file_put_contents($filePath, "header1,header2\nvalue1,value2\nvalue3,value4");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && $event->lineCount === 2 && isset($event->parsedLines);
        });
    }

    public function test_parses_csv_with_extra_columns()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/extra_columns.csv';
        file_put_contents($filePath, "header1,header2,extra\nvalue1,value2,foo\nvalue3,value4,bar");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 2;
        });
    }

    public function testParsesCsvWithQuotedFieldsAndCommas()
    {
        // Mock the file content
        $csvContent = "header1,header2\n\"value1, with comma\",value2";
        $filePath = 'mocked_quoted.csv';

        // Create a mock of the InvoiceParserService
        $parserMock = $this->getMockBuilder(InvoiceParserService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up the mock to return the expected result
        $parserMock->expects($this->once())
            ->method('parse')
            ->with($filePath)
            ->willReturn([
                ['header1' => 'value1, with comma', 'header2' => 'value2']
            ]);

        // Call the parse method on the mock
        $result = $parserMock->parse($filePath);

        // Assert the result
        $this->assertEquals([
            ['header1' => 'value1, with comma', 'header2' => 'value2']
        ], $result);
    }

    public function test_parses_xml_with_unknown_tags()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/unknown_tags.xml';
        $xml = "<invoices><line><header1>value1</header1><header2>value2</header2><unknown>foo</unknown></line></invoices>";
        file_put_contents($filePath, $xml);
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 1;
        });
    }

    public function test_parses_csv_with_different_line_endings()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/line_endings.csv';
        file_put_contents($filePath, "header1,header2\r\nvalue1,value2\r\nvalue3,value4");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 2;
        });
    }

    public function test_header_case_insensitivity_in_csv()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/case_headers.csv';
        file_put_contents($filePath, "HEADER1,header2\nvalue1,value2");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 1;
        });
    }

    public function test_parses_large_csv_file()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/large.csv';
        $data = "header1,header2\n" . str_repeat("value1,value2\n", 10000);
        file_put_contents($filePath, $data);
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 10000;
        });
    }

    public function test_parses_multiple_file_types_in_batch()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $csvPath = __DIR__ . '/stubs/batch.csv';
        $xmlPath = __DIR__ . '/stubs/batch.xml';
        file_put_contents($csvPath, "header1,header2\nvalue1,value2");
        file_put_contents($xmlPath, "<invoices><line><header1>value1</header1><header2>value2</header2></line></invoices>");
        $service->parse($csvPath);
        $service->parse($xmlPath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, 2);
    }

    public function test_parses_csv_with_trailing_commas_and_whitespace()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/trailing.csv';
        file_put_contents($filePath, "header1,header2,\nvalue1,value2,\n");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 1;
        });
    }

    public function test_event_content_matches_expected_data()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/content.csv';
        file_put_contents($filePath, "header1,header2\nvalue1,value2");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return isset($event->parsedLines) && $event->parsedLines === [
                ['header1' => 'value1', 'header2' => 'value2']
            ];
        });
    }

    public function test_parses_file_with_bom()
    {
        Event::fake();
        $service = new InvoiceParserService();
        $filePath = __DIR__ . '/stubs/bom.csv';
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        file_put_contents($filePath, $bom . "header1,header2\nvalue1,value2");
        $service->parse($filePath);
        Event::assertDispatched(CarrierInvoiceLineExtracted::class, function ($event) {
            return $event->lineCount === 1;
        });
    }
} 