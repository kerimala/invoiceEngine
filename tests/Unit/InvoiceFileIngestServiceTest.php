<?php
// tests/Unit/InvoiceFileIngestServiceTest.php

use Tests\TestCase;
uses(TestCase::class);

use App\Services\InvoiceFileIngestService;
use App\Services\SftpClientInterface;
use Exception;

beforeEach(function () {
    // Each test will instantiate its own service or fake client as needed.
});

it('throws an exception if the file does not exist', function () {
    $svc = new InvoiceFileIngestService();

    // Path that definitely does not exist:
    $badPath = base_path('invoices/does-not-exist.pdf');

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Invoice file not found at path");

    $svc->ingest($badPath);
});

it('returns the real path when a valid file exists', function () {
    $svc = new InvoiceFileIngestService();

    // Create a dummy file under temp_invoices using helper
    $dummyFile = makeTempFile('dummy-invoice.txt', 'DUMMY INVOICE CONTENT');

    $realPath = $svc->ingest($dummyFile);
    expect($realPath)->toBeRealPath();

    cleanTempFile($dummyFile);
});

it('throws if the file exists but is not readable', function () {
    $svc = new InvoiceFileIngestService();

    // Create a file and make it unreadable
    $tempDir = ensureTempDir();
    $lockedFile = $tempDir . '/locked-invoice.txt';
    file_put_contents($lockedFile, 'CANNOT READ THIS');
    chmod($lockedFile, 0000);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('not readable');

    $svc->ingest($lockedFile);

    // Restore permissions and clean up:
    chmod($lockedFile, 0644);
    cleanTempFile($lockedFile);
});

it('throws if the target path is a directory instead of a file', function () {
    $svc = new InvoiceFileIngestService();

    $tempDir = ensureTempDir();
    // Pass the directory path itself
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('not a valid file');

    $svc->ingest($tempDir);
});

// ---------------------------------------------
// Tests for supported/unsupported formats
// ---------------------------------------------

it('throws if the file has an unsupported extension', function () {
    $svc = new InvoiceFileIngestService();

    $badFile = makeTempFile('invalid-format.jpg', 'NOT AN INVOICE');

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('unsupported file format');

    $svc->ingest($badFile);

    cleanTempFile($badFile);
});

it('accepts lowercase CSV, XML, and Excel file formats', function () {
    $svc = new InvoiceFileIngestService();

    $allowed = [
        'invoice.csv',
        'invoice.xml',
        'invoice.xlsx',
        'invoice.xls',
    ];

    foreach ($allowed as $filename) {
        $path = makeTempFile($filename, 'DUMMY CONTENT');

        $realPath = $svc->ingest($path);
        expect($realPath)->toBeRealPath();

        cleanTempFile($path);
    }
});

it('accepts uppercase or mixed-case extensions like .CSV and .Xml', function () {
    $svc = new InvoiceFileIngestService();

    $cases = [
        'invoice.CSV',
        'invoice.Xml',
        'invoice.XLSX',
        'invoice.XlS',
    ];

    foreach ($cases as $filename) {
        $path = makeTempFile($filename, 'DUMMY CONTENT');

        $realPath = $svc->ingest($path);
        expect($realPath)->toBeRealPath();

        cleanTempFile($path);
    }
});

it('rejects files with double extensions like invoice.csv.bak', function () {
    $bad = makeTempFile('invoice.csv.bak', 'GARBAGE');

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('unsupported file format');

    $svc = new InvoiceFileIngestService();
    $svc->ingest($bad);

    cleanTempFile($bad);
});

// ---------------------------------------------
// Tests for fetching invoices via SFTP
// ---------------------------------------------

it('fetches a remote file from SFTP and returns the local path (auto-creates directory)', function () {
    $fakeSftp = fakeSftpAlwaysSucceeds();
    $svc = new InvoiceFileIngestService($fakeSftp);

    // Ensure the target local folder does NOT exist so service must create it
    $localDir = storage_path('sftp_invoices');
    if (file_exists($localDir)) {
        $files = glob($localDir . '/*');
        foreach ($files as $file) unlink($file);
        rmdir($localDir);
    }

    $remotePath = '/remote/invoices/invoice123.csv';
    $localPath = $svc->ingestFromSftp($remotePath, $localDir);

    expect($localPath)->toBeRealPath();
    expect(file_get_contents($localPath))->toBe('REMOTE INVOICE CONTENT');

    cleanTempFile($localPath);
});

it('throws when SFTP client fails to fetch the remote file', function () {
    $fakeBadSftp = fakeSftpAlwaysFails();
    $svc = new InvoiceFileIngestService($fakeBadSftp);

    $localDir = storage_path('sftp_fail');
    if (file_exists($localDir)) {
        $files = glob($localDir . '/*');
        foreach ($files as $file) unlink($file);
        rmdir($localDir);
    }

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Failed to fetch remote file via SFTP');

    $svc->ingestFromSftp('/remote/invoices/missing.csv', $localDir);
});

it('downloads all files in a remote directory via SFTP', function () {
    $fakeBatchSftp = fakeSftpBatch();
    $svc = new InvoiceFileIngestService($fakeBatchSftp);

    $batchDir = storage_path('sftp_batch');
    if (file_exists($batchDir)) {
        $files = glob($batchDir . '/*');
        foreach ($files as $file) unlink($file);
        rmdir($batchDir);
    }

    $downloaded = $svc->ingestAllFromSftp('/remote/dir/May2025', $batchDir);
    expect($downloaded)->toHaveCount(2);
    foreach ($downloaded as $path) {
        expect($path)->toBeRealPath();
        expect(file_get_contents($path))->toContain('DUMMY: /remote/dir/May2025/');
        cleanTempFile($path);
    }
});

it('throws when SFTP authentication fails', function () {
    // Fake SFTP client that fails to authenticate forces get() or ls() as stubs
    $fakeAuthFailSftp = new class implements SftpClientInterface {
        public function ls(string $remoteDir): array {
            return [];
        }
        public function connect(string $host, string $user, string $pass): bool {
            return false;
        }
        public function get(string $remote, string $local): bool {
            return false;
        }
    };

    $svc = new InvoiceFileIngestService($fakeAuthFailSftp);
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('SFTP authentication failed');

    $svc->ingestFromSftp('/remote/file.csv', storage_path('temp_invoices'));
});