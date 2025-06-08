<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/


function something()
{
    // ..
}

// ---------------------------------------------
// Custom helper functions for InvoiceEngine tests
// ---------------------------------------------

/**
 * Ensure a writable temporary directory exists under storage_path($subdir).
 * Returns the full path to that directory.
 */
function ensureTempDir(string $subdir = 'temp_invoices'): string
{
    $full = storage_path($subdir);
    if (! file_exists($full)) {
        mkdir($full, 0755, true);
    }
    return $full;
}

/**
 * Create a dummy file named $filename inside storage_path($subdir)
 * with the provided $content. Returns the absolute path to the file.
 */
function makeTempFile(string $filename, string $content, string $subdir = 'temp_invoices'): string
{
    $dir = ensureTempDir($subdir);
    $path = $dir . '/' . $filename;
    file_put_contents($path, $content);
    return $path;
}

/**
 * Remove a file at $filePath if it exists, and delete its parent directory if it becomes empty.
 */
function cleanTempFile(string $filePath): void
{
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    $dir = dirname($filePath);
    if (is_dir($dir) && count(scandir($dir)) === 2) {
        rmdir($dir);
    }
}

/**
 * Bind a fake AgreementService that always returns the given integer percentage.
 */
function bindFakeAgreementInt(int $percent): void
{
    $fake = new class($percent) implements \App\Services\AgreementService {
        private int $pct;
        public function __construct(int $p) { $this->pct = $p; }
        public function getMultiplier(string $customerId): int
        {
            return $this->pct;
        }
    };
    app()->bind(\App\Services\AgreementService::class, get_class($fake));
}

/**
 * Bind a fake AgreementService whose getMultiplier() is defined by a callback.
 * The callback receives the $customerId and returns an integer percentage.
 */
function bindFakeAgreementCallback(callable $callback): void
{
    $fake = new class($callback) implements \App\Services\AgreementService {
        private $cb;
        public function __construct(callable $c) { $this->cb = $c; }
        public function getMultiplier(string $customerId): int
        {
            return ($this->cb)($customerId);
        }
    };
    app()->bind(\App\Services\AgreementService::class, get_class($fake));
}

/**
 * Return a fake SftpClientInterface that always succeeds on get():
 * writes "REMOTE INVOICE CONTENT" to $localPath.
 */
function fakeSftpAlwaysSucceeds(): \App\Services\SftpClientInterface
{
    return new class implements \App\Services\SftpClientInterface {
        public function connect(string $host, string $user, string $pass): bool { return true; }
        public function get(string $remotePath, string $localPath): bool {
            file_put_contents($localPath, 'REMOTE INVOICE CONTENT');
            return true;
        }
        public function ls(string $remoteDir): array { return []; }
    };
}

/**
 * Return a fake SftpClientInterface whose get() always returns false.
 */
function fakeSftpAlwaysFails(): \App\Services\SftpClientInterface
{
    return new class implements \App\Services\SftpClientInterface {
        public function connect(string $host, string $user, string $pass): bool { return true; }
        public function get(string $remotePath, string $localPath): bool { return false; }
        public function ls(string $remoteDir): array { return []; }
    };
}

/**
 * Return a fake SftpClientInterface for batch downloads:
 * ls() returns ['inv1.csv','inv2.xml'], and get() writes dummy content.
 */
function fakeSftpBatch(): \App\Services\SftpClientInterface
{
    return new class implements \App\Services\SftpClientInterface {
        public function connect(string $host, string $user, string $pass): bool { return true; }
        public function ls(string $remoteDir): array { return ['inv1.csv', 'inv2.xml']; }
        public function get(string $remotePath, string $localPath): bool {
            file_put_contents($localPath, "DUMMY: $remotePath");
            return true;
        }
    };
}
