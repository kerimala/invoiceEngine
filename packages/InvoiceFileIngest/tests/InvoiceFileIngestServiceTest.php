<?php

namespace Packages\InvoiceFileIngest\Tests;

use Illuminate\Support\Facades\Event;
use Packages\InvoiceFileIngest\Services\InvoiceFileIngestService;
use Packages\InvoiceFileIngest\Events\FileStored;
use Tests\TestCase;

class InvoiceFileIngestServiceTest extends TestCase
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
        // Clean up stub files
        array_map('unlink', glob(__DIR__ . '/stubs/*'));
        parent::tearDown();
    }

    public function test_ingests_valid_file_and_emits_event()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/valid_invoice.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');

        $service->ingest($filePath);

        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }

    public function test_throws_exception_for_missing_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $service->ingest('/nonexistent/file.csv');
    }

    public function test_throws_exception_for_unsupported_file_type()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invalid_invoice.txt';
        file_put_contents($filePath, 'dummy');
        $service->ingest($filePath);
    }

    public function test_throws_exception_for_file_with_invalid_permissions()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/protected_invoice.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        chmod($filePath, 0000); // Remove all permissions
        try {
            $service->ingest($filePath);
        } finally {
            chmod($filePath, 0644); // Restore permissions so we can clean up
        }
    }

    public function test_ingests_csv_file_with_various_extensions()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $extensions = ['csv', 'CSV', 'Csv', 'cSv'];
        foreach ($extensions as $ext) {
            $filePath = __DIR__ . "/stubs/test_file.$ext";
            file_put_contents($filePath, 'header1,header2\nvalue1,value2');
            $service->ingest($filePath);
            Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
                return $event->filePath === $filePath;
            });
        }
    }

    public function test_rejects_file_with_double_extension()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invoice.csv.bak';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
    }

    public function test_ingests_xml_file()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invoice.xml';
        file_put_contents($filePath, '<xml>test</xml>');
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }

    public function test_rejects_empty_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/empty.csv';
        file_put_contents($filePath, '');
        $service->ingest($filePath);
    }

    public function test_ingests_file_with_mixed_case_extension()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invoice.CsV';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }

    public function test_emits_event_with_correct_metadata()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/valid_invoice.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && isset($event->size) && $event->size === filesize($filePath);
        });
    }

    public function test_ingests_multiple_files_in_sequence()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $files = [
            __DIR__ . '/stubs/file1.csv',
            __DIR__ . '/stubs/file2.csv',
            __DIR__ . '/stubs/file3.xml',
        ];
        foreach ($files as $filePath) {
            file_put_contents($filePath, 'header1,header2\nvalue1,value2');
            $service->ingest($filePath);
            Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
                return $event->filePath === $filePath;
            });
        }
    }

    public function test_throws_for_directory_instead_of_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $dirPath = __DIR__ . '/stubs/some_dir';
        mkdir($dirPath);
        try {
            $service->ingest($dirPath);
        } finally {
            rmdir($dirPath);
        }
    }

    public function test_ingests_large_file()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/large.csv';
        $data = str_repeat('header1,header2\nvalue1,value2\n', 10000); // Large file
        file_put_contents($filePath, $data);
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }

    public function test_emits_event_with_custom_metadata()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/custommeta.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        // Assume ingest can take optional metadata
        $service->ingest($filePath, ['source' => 'test']);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath && isset($event->metadata['source']) && $event->metadata['source'] === 'test';
        });
    }

    public function test_throws_for_file_with_no_extension()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/noextension';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
    }

    public function test_throws_for_file_with_spaces_in_extension()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invoice .csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
    }

    public function test_ingests_file_with_leading_dot_in_name()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/.hidden.csv';
        file_put_contents($filePath, 'header1,header2\nvalue1,value2');
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }

    public function test_throws_for_symlink_to_invalid_file()
    {
        $this->expectException(\Exception::class);
        $service = new InvoiceFileIngestService();
        $target = __DIR__ . '/stubs/missing.csv';
        $link = __DIR__ . '/stubs/link.csv';
        @unlink($link);
        symlink($target, $link);
        try {
            $service->ingest($link);
        } finally {
            @unlink($link);
        }
    }

    public function test_ingests_xlsx_file()
    {
        Event::fake();
        $service = new InvoiceFileIngestService();
        $filePath = __DIR__ . '/stubs/invoice.xlsx';
        file_put_contents($filePath, 'dummy xlsx content');
        $service->ingest($filePath);
        Event::assertDispatched(FileStored::class, function ($event) use ($filePath) {
            return $event->filePath === $filePath;
        });
    }
}