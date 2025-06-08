<?php
namespace App\Services;

use Exception;
use PhpParser\Node\Expr\FuncCall;

class InvoiceFileIngestService
{
    public string $ingestDir = 'invoices';
    protected ?SftpClientInterface $sftpClient;

    public function __construct(SftpClientInterface $sftpClient = null)
    {
        // Allow null so local‐only ingest still works:
        $this->sftpClient = $sftpClient;
    }

    /**
     * Ingest a local file: check extension, existence, readability, then return real path.
     */
    public function ingest(string $filePath): string
    {
        throw new Exception('InvoiceFileIngestService::ingest() not implemented.');
    }

    public function setIngestDir(string $dir): self
    {
        $this->ingestDir = $dir;
        return $this;
    }

    /**
     * Fetch one remote file via SFTP into $localDir, creating $localDir if needed.
     */
    public function ingestFromSftp(string $remotePath, string $localDir): string
    {
        throw new Exception('InvoiceFileIngestService::ingestFromSftp() not implemented.');
    }

    /**
     * Fetch all files under a remote directory via SFTP into $localDir.
     * Return an array of local paths.
     */
    public function ingestAllFromSftp(string $remoteDir, string $localDir): array
    {
        throw new Exception('InvoiceFileIngestService::ingestAllFromSftp() not implemented.');
    }
}